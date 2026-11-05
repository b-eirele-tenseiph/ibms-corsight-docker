<?php

namespace App\Services\ElectricMonitoring;

use App\Models\HourlyConsumption;
use App\Models\LorawanDevice;
use Carbon\Carbon;
use App\Services\Utils\Downsampler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ElectricMonitoringService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $threshold            (1.0)  Set a global variable for the threshold.
    // loadTotalData         (2.0)  Load the total data from the electric meter.
    // loadConsumptionData   (3.0)  Load the consumption data from the electric
    //                              meter.
    // loadCurrentData       (4.0)  Load the current data from the electric
    //                              meter.
    // loadVoltageData       (5.0)  Load the voltage data from the electric
    //                              meter.
    // loadPowerData         (6.0)  Load the power data from the electric meter.
    // loadPowerFactorData   (7.0)  Load the power factor data from the electric
    //                              meter
    // loadFrequencyData     (8.0)  Load the frequency data from the electric
    //                              meter.
    // loadDataInsights      (9.0)  Load the data insights from the electric
    //                              meter.
    // parseTimeRange        (10.0) Parse time parameter into start and end
    //                              time range.
    // buildPhaseResults     (11.0) Build phase results array with names from
    //                              device context.
    // getVoltageTypes       (12.0) Get voltage types based on phase
    //                              configuration.
    // getBetweenPhaseNames  (13.0) Get custom names for between-phase voltage
    //                              measurements.
    // computeThreshold      (14.0) Compute the threshold depending on the time.
    // processDownsample     (15.0) Process the data downsample.
    // getResolution         (16.0) Determine the appropriate resolution based
    //                              on the time range.
    // queryWithResolution   (17.0) Query the electric meter data with
    //                              appropriate resolution and pre-aggregation
    //                              for non-minute resolutions.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $threshold
     * <Function> Set a global variable for the threshold.
     *
     * @var integer
     */
    private $threshold;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> loadTotalData
     * <Function> Load the total data from the electric meter.
     *
     * @param string $device_id
     * @return array
     */
    public function loadTotalData(string $device_id): array
    {
        $device = LorawanDevice::where('id', $device_id)
            ->with('latestData')
            ->first();

        return [
            'id' => $device->id,
            'data' => collect($device->latestData?->context)
                ->mapWithKeys(fn($item, $key) => [$key => $item['value']])
                ->toArray()
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> loadConsumptionData
     * <Function> Load the consumption data from the electric meter.
     *
     * @param string $device_id
     * @param mixed $time
     * @return array
     */
    public function loadConsumptionData(string $device_id, mixed $time): array
    {
        [$startTime, $endTime] = $this->parseTimeRange($time);
        $this->computeThreshold($startTime->diffInHours($endTime));

        $types = [
            'phase_a' => 'active_energy_a',
            'phase_b' => 'active_energy_b',
            'phase_c' => 'active_energy_c'
        ];

        $device = LorawanDevice::select('id', 'name', 'context')->find($device_id);

        $allPhases = HourlyConsumption::where('device_id', $device_id)
            ->whereBetween('date', [$startTime, $endTime])
            ->whereIn('consumption->type', array_values($types))
            ->get();

        $phaseResults = $this->buildPhaseResults($types, $device);

        return [
            'id' => $device_id,
            'unit' => 'kWh',
            'title' => __('defaults.energy_consumption'),
            'series' => array_values($this->processDownsample($allPhases, $phaseResults))
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> loadCurrentData
     * <Function> Load the current data from the electric meter.
     *
     * @param string $device_id
     * @param mixed $time
     * @return array
     */
    public function loadCurrentData(string $device_id, mixed $time): array
    {
        [$startTime, $endTime] = $this->parseTimeRange($time, false);
        $this->computeThreshold($startTime->diffInHours($endTime));

        $types = [
            'phase_a' => 'electricity_a',
            'phase_b' => 'electricity_b',
            'phase_c' => 'electricity_c'
        ];

        $device = LorawanDevice::select('id', 'name', 'context')->find($device_id);

        $resolution = $this->getResolution($startTime, $endTime);

        $allPhases = $this->queryWithResolution(
            $device_id,
            array_values($types),
            $startTime,
            $endTime,
            $resolution
        );

        $phaseResults = $this->buildPhaseResults($types, $device);

        return [
            'id' => $device_id,
            'unit' => 'A',
            'title' => __('defaults.current'),
            'series' => array_values($this->processDownsample($allPhases, $phaseResults))
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> loadVoltageData
     * <Function> Load the voltage data from the electric meter.
     *
     * @param string $device_id
     * @param mixed $time
     * @param string $phase
     * @return array
     */
    public function loadVoltageData(string $device_id, mixed $time, string $phase): array
    {
        [$startTime, $endTime] = $this->parseTimeRange($time, false);
        $this->computeThreshold($startTime->diffInHours($endTime));

        $types = $this->getVoltageTypes($phase);

        $device = LorawanDevice::select('id', 'name', 'context')->find($device_id);

        $resolution = $this->getResolution($startTime, $endTime);

        $allPhases = $this->queryWithResolution(
            $device_id,
            array_values($types),
            $startTime,
            $endTime,
            $resolution
        );

        $phaseResults = $phase === 'between_phase'
            ? $this->buildPhaseResults($types, $device, $this->getBetweenPhaseNames())
            : $this->buildPhaseResults($types, $device);

        return [
            'id' => $device_id,
            'unit' => 'V',
            'title' => __('defaults.voltage'),
            'series' => array_values($this->processDownsample($allPhases, $phaseResults)),
        ];
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> loadPowerData
     * <Function> Load the power data from the electric meter.
     *
     * @param string $device_id
     * @param mixed $time
     * @return array
     */
    public function loadPowerData(string $device_id, mixed $time): array
    {
        [$startTime, $endTime] = $this->parseTimeRange($time, false);
        $this->computeThreshold($startTime->diffInHours($endTime));

        $types = [
            'phase_a' => 'active_power_a',
            'phase_b' => 'active_power_b',
            'phase_c' => 'active_power_c'
        ];

        $device = LorawanDevice::select('id', 'name', 'context')->find($device_id);

        $resolution = $this->getResolution($startTime, $endTime);

        $allPhases = $this->queryWithResolution(
            $device_id,
            array_values($types),
            $startTime,
            $endTime,
            $resolution
        );

        $phaseResults = $this->buildPhaseResults($types, $device);

        return [
            'id' => $device_id,
            'unit' => 'kW',
            'title' => __('defaults.power'),
            'series' => array_values($this->processDownsample($allPhases, $phaseResults))
        ];
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> loadPowerFactorData
     * <Function> Load the power factor data from the electric meter.
     *
     * @param string $device_id
     * @param mixed $time
     * @return array
     */
    public function loadPowerFactorData(string $device_id, mixed $time): array
    {
        [$startTime, $endTime] = $this->parseTimeRange($time, false);
        $this->computeThreshold($startTime->diffInHours($endTime));

        $types = [
            'phase_a' => 'power_factor_a',
            'phase_b' => 'power_factor_b',
            'phase_c' => 'power_factor_c'
        ];

        $device = LorawanDevice::select('id', 'name', 'context')->find($device_id);

        $resolution = $this->getResolution($startTime, $endTime);

        $allPhases = $this->queryWithResolution(
            $device_id,
            array_values($types),
            $startTime,
            $endTime,
            $resolution
        );

        $phaseResults = $this->buildPhaseResults($types, $device);

        return [
            'id' => $device_id,
            'unit' => null,
            'title' => __('defaults.power_factor'),
            'series' => array_values($this->processDownsample($allPhases, $phaseResults)),
        ];
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> loadFrequencyData
     * <Function> Load the frequency data from the electric meter.
     *
     * @param string $device_id
     * @param mixed $time
     * @return array
     */
    public function loadFrequencyData(string $device_id, mixed $time): array
    {
        [$startTime, $endTime] = $this->parseTimeRange($time, false);
        $this->computeThreshold($startTime->diffInHours($endTime));

        $resolution = $this->getResolution($startTime, $endTime);

        $frequency = $this->queryWithResolution(
            $device_id,
            ['frequency'],
            $startTime,
            $endTime,
            $resolution
        );

        $phaseResults = [
            'frequency' => [
                'name' => 'Frequency',
                'data' => []
            ]
        ];

        return [
            'id' => $device_id,
            'unit' => 'Hz',
            'title' => __('defaults.frequency'),
            'series' => array_values($this->processDownsample($frequency, $phaseResults))
        ];
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> loadDataInsights
     * <Function> Load the data insights from the electric meter.
     *
     * @param string $device_id
     * @return array
     */
    public function loadDataInsights(string $device_id): array
    {
        $device = LorawanDevice::with('latestData')->find($device_id);

        return [
            'voltage_unbalance' => $device->latestData->context['voltage_unbalance']['value'] ?? 0,
            'total_power_factor' => $device->latestData->context['power_factor_total']['value'] ?? 0,
            'current_unbalance' => $device->latestData->context['current_unbalance']['value'] ?? 0,
            'frequency' => $device->latestData->context['frequency']['value'] ?? 0
        ];
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> parseTimeRange
     * <Function> Parse time parameter into start and end time range.
     *
     * @param mixed $time
     * @param bool $endOfDay
     * @return array
     */
    private function parseTimeRange(mixed $time, bool $endOfDay = true): array
    {
        if (is_array($time)) {
            return [
                Carbon::parse($time['start'], 'Asia/Manila')->startOfDay()->setTimezone('UTC'),
                Carbon::parse($time['end'], 'Asia/Manila')->endOfDay()->setTimezone('UTC')
            ];
        }

        $time--;

        return [
            now()->subHours($time)->startOfHour(),
            $endOfDay ? now()->startOfHour() : now()
        ];
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> buildPhaseResults
     * <Function> Build phase results array with names from device context.
     *
     * @param array $types
     * @param mixed $device
     * @param array|null $customNames
     * @return array
     */
    private function buildPhaseResults(array $types, $device, ?array $customNames = null): array
    {
        return collect($types)->mapWithKeys(function ($type, $key) use ($device, $customNames) {
            $name = $customNames[$type] ?? $device?->context[$key] ?? $device?->name;

            return [
                $type => [
                    'name' => $name,
                    'data' => []
                ]
            ];
        })->toArray();
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> getVoltageTypes
     * <Function> Get voltage types based on phase configuration.
     *
     * @param string $phase
     * @return array
     */
    private function getVoltageTypes(string $phase): array
    {
        return match ($phase) {
            'distinct_phase' => [
                'phase_a' => 'voltage_a',
                'phase_b' => 'voltage_b',
                'phase_c' => 'voltage_c'
            ],
            'between_phase' => [
                'phase_a' => 'voltage_ab',
                'phase_b' => 'voltage_bc',
                'phase_c' => 'voltage_ca'
            ],
            default => []
        };
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> getBetweenPhaseNames
     * <Function> Get custom names for between-phase voltage measurements.
     *
     * @return array
     */
    private function getBetweenPhaseNames(): array
    {
        return [
            'voltage_ab' => 'Phase AB',
            'voltage_bc' => 'Phase BC',
            'voltage_ca' => 'Phase CA'
        ];
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> $computeThreshold
     * <Function> Compute the threshold depending on the time.
     *
     * @param integer $time
     * @return void
     */
    public function computeThreshold(int $time): void
    {
        if ($time <= 6) {
            $threshold = 300;
        } elseif ($time > 6 && $time <= 24) {
            $threshold = 400;
        } elseif ($time > 24 && $time <= 168) {
            $threshold = 500;
        } elseif ($time > 168) {
            $threshold = 600;
        }

        $this->threshold = $threshold;
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> processDownsample
     * <Function> Process the data downsample.
     *
     *  @param \Illuminate\Database\Eloquent\Collection $allPhases
     *  @param array $phaseResults
     *  @return array
     */
    public function processDownsample(Collection $allPhases, array $phaseResults): array
    {
        foreach ($allPhases as $data) {
            if ($data->type === null) {
                $timestamp = Carbon::parse($data->date)->timestamp;
                $value = $data->consumption['value'];
                $phaseKey = $data->consumption['type'];
            } else {
                $context = json_decode($data->context, true);
                $timestamp = Carbon::parse($data->received_at)->timestamp;
                $value = $context['value'];
                $phaseKey = $data->type;
            }

            if (is_string($value)) {
                $value = str_replace(',', '.', $value);
            }

            $phaseResults[$phaseKey]['data'][] = [(int) $timestamp, (float) $value];
        }

        return array_map(function ($phase, $result) {
            return [
                'id' => $phase,
                'name' => $result['name'],
                'data' => array_map(
                    fn($point) => [Carbon::createFromTimestamp($point[0]), round($point[1], 2)],
                    Downsampler::sample($result['data'] ?? [], $this->threshold)
                )
            ];
        }, array_keys($phaseResults), $phaseResults);
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> getResolution
     * <Function> Determine the appropriate resolution based on the time range.
     *
     * @param \Carbon\Carbon $startTime
     * @param \Carbon\Carbon $endTime
     * @return string
     */
    private function getResolution(Carbon $startTime, Carbon $endTime): string
    {
        $hours = $startTime->diffInHours($endTime);

        return match (true) {
            $hours <= 168  => 'minute', // up to 7 days
            $hours <= 744  => 'hour',   // up to 1 month
            default        => 'day'
        };
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> queryWithResolution
     * <Function> Query the electric meter data with appropriate resolution and pre-aggregation for non-minute resolutions.
     *
     * @param string $deviceId
     * @param array $types
     * @param \Carbon\Carbon $startTime
     * @param \Carbon\Carbon $endTime
     * @param string $resolution
     *
     * @return \Illuminate\Support\Collection
     */
    private function queryWithResolution(
        string $deviceId,
        array $types,
        Carbon $startTime,
        Carbon $endTime,
        string $resolution
    ): Collection {

        if ($resolution === 'minute') {
            // For minute resolution, we fetch raw data without pre-aggregation
            return DB::table('electric_meter_data')
                ->select('type', 'context', 'received_at')
                ->where('device_id', $deviceId)
                ->whereIn('type', $types)
                ->whereBetween('received_at', [$startTime, $endTime])
                ->get();
        }

        $format = $resolution === 'hour'
            ? '%Y-%m-%d %H:00:00'
            : '%Y-%m-%d 00:00:00';

        // For hour and day resolutions, we perform pre-aggregation in the query to reduce the amount of data processed in PHP
        return DB::table('electric_meter_data')
            ->select(
                'type',
                DB::raw('DATE_FORMAT(received_at, "' . $format . '") as received_at'),
                DB::raw('CONCAT(\'{"value":\', AVG(JSON_UNQUOTE(JSON_EXTRACT(context, "$.value"))), \'}\') as context')
            )
            ->where('device_id', $deviceId)
            ->whereIn('type', $types)
            ->whereBetween('received_at', [$startTime, $endTime])
            ->groupBy('type', DB::raw('DATE_FORMAT(received_at, "' . $format . '")'))
            ->get();
    }
}
