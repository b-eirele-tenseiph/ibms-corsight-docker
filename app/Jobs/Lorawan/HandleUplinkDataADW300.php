<?php

namespace App\Jobs\Lorawan;

use App\Contracts\Lorawan\PayloadContract as LorawanContract;
use App\Events\Lorawan\NewElectricMeterData;
use App\Jobs\Lorawan\ElectricMeter\MonitorEnergyConsumption;
use App\Models\{DeviceLatestData, LorawanDevice};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\{DB, Log};
use Throwable;

class HandleUplinkDataADW300 extends LorawanContract implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // ENERGY_TYPES            (1.0)  Energy types that should trigger the job.
    // VOLTAGE_PHASE           (2.0)  Valid voltage phase types.
    // VOLTAGE_LINE            (3.0)  Valid voltage line types.
    // CURRENT                 (4.0)  Valid current types.
    // LATEST_FIELDS           (5.0)  Fields to be cached in latest device data.
    // $calculatedMetrics      (6.0)  Store calculated metrics.
    // __construct             (7.0)  Create a new job instance and set the
    //                                global variables.
    // handle                  (8.0)  Execute the job logic.
    // failed                  (9.0)  Handle job failure.
    // saveData                (10.0) Save the decoded data to the database.
    // computeTotals           (11.0) Compute the average voltage within the
    //                                line and phases and total current.
    // updateLatestData        (12.0) Refresh cached latest device data.
    // broadcastData           (13.0) Broadcast the newly saved data to
    //                                listeners.
    // calculatedBalance       (14.0) Prepare metrics for calculating load
    //                                balance.
    // dispatchEnergyMonitor   (15.0) Filter and dispatch the
    //                                MonitorEnergyConsumption job.
    // calculateLoadBalance    (16.0) Calculate load balance percentage for
    //                                given types.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> ENERGY_TYPES
     * <Function> Energy types that should trigger the job.
     *
     * @var array
     */
    private const ENERGY_TYPES = [
        'positive_active_energy',
        'active_energy_a',
        'active_energy_b',
        'active_energy_c'
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> VOLTAGE_PHASE
     * <Function> Valid voltage phase types.
     *
     * @var array
     */
    private const VOLTAGE_PHASE = ['voltage_a', 'voltage_b', 'voltage_c'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> VOLTAGE_LINE
     * <Function> Valid voltage line types.
     *
     * @var array
     */
    private const VOLTAGE_LINE = ['voltage_ab', 'voltage_bc', 'voltage_ca'];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> CURRENT
     * <Function> Valid current types.
     *
     * @var array
     */
    private const CURRENT = ['electricity_a', 'electricity_b', 'electricity_c'];


    /**
     * <Layer number> (5.0)
     *
     * <Processing name> LATEST_FIELDS
     * <Function> Fields to be cached in latest device data.
     *
     * @var array
     */
    private const LATEST_FIELDS = [
        'positive_active_energy',
        'average_voltage_phase',
        'average_voltage_line',
        'power_factor_total',
        'total_active_power',
        'total_current',
        'frequency'
    ];

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $calculatedMetrics
     * <Function> Store calculated metrics.
     *
     * @var array
     */
    private array $calculatedMetrics = [];

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance and set the global variables.
     *
     * @param \App\Models\LorawanDevice $device
     * @param string $payload
     * @return void
     */
    public function __construct(LorawanDevice $device, string $payload)
    {
        $this->device = $device;
        $this->payload = $payload;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> handle
     * <Function> Execute the job logic.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->buildDecoderClass()->decodePayload();

        if ($this->decoded->isNotEmpty()) {
            $this->saveData()
                ->broadcastData();
        }
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> failed
     * <Function> Handle job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Job Failed', [
            'task' => __CLASS__,
            'exception' => [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]
        ]);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> saveData
     * <Function> Save the decoded data to the database.
     *
     * @return self
     */
    private function saveData(): self
    {
        $this->transformData();
        $this->computeTotals();

        $rows = $this->decoded->map(fn($r) => [
            'device_id' => $this->device->id,
            'transmission' => $r['transmission'],
            'type' => $r['type'],
            'context' => json_encode($r['context']),
            'received_at' => $r['received_at']
        ])->all();

        $receivedAt = $rows[0]['received_at'] ?? null;

        DB::transaction(function () use ($rows, $receivedAt) {
            $this->device->electricMeterData()->insert($rows);
            $this->device->touchQuietly();

            $this->dataCollection = $this->device->electricMeterData()
                ->where('device_id', $this->device->id)
                ->where('received_at', $receivedAt)
                ->get();

            $this->calculatedMetrics = $this->calculatedBalance();

            $this->updateLatestData();
        });

        $this->dispatchEnergyMonitor();

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> computeTotals
     * <Function> Compute the average voltage within the line and phases and total current.
     *
     * @return self
     */
    private function computeTotals(): self
    {
        $filtered = $this->decoded->whereIn('type', [...self::VOLTAGE_PHASE, ...self::VOLTAGE_LINE, ...self::CURRENT]);

        if ($filtered->isEmpty()) return $this;

        $totals = ['phase' => 0, 'line' => 0, 'current' => 0];

        foreach ($filtered as $item) {
            $val = (float) $item['context']['value'];

            if (in_array($item['type'], self::VOLTAGE_PHASE)) $totals['phase'] += $val / 3;
            if (in_array($item['type'], self::VOLTAGE_LINE)) $totals['line'] += $val / 3;
            if (in_array($item['type'], self::CURRENT)) $totals['current'] += $val ** 2;
        }

        $computation = [
            ['type' => 'average_voltage_phase', 'value' => $totals['phase'], 'unit' => 'V'],
            ['type' => 'average_voltage_line', 'value' => $totals['line'], 'unit' => 'V'],
            ['type' => 'total_current', 'value' => sqrt($totals['current']), 'unit' => 'A']
        ];

        $computed = collect($computation)
            ->map(fn($item) => [
                'transmission' => 'uplink',
                'type' => $item['type'],
                'context' => ['value' => round($item['value'], 2), 'unit' => $item['unit']],
                'received_at' => $filtered->first()['received_at']
            ]);

        $this->decoded = $this->decoded->concat($computed);

        return $this;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> updateLatestData
     * <Function> Refresh cached latest device data.
     *
     * @return void
     */
    private function updateLatestData(): void
    {
        $latest = $this->dataCollection
            ->filter(fn($d) => in_array($d->type, self::LATEST_FIELDS))
            ->mapWithKeys(fn($d) => [$d->type => [
                'value' => $d->context['value'] ?? null,
                'unit' => $d->context['unit'] ?? null
            ]])
            ->merge(
                collect($this->calculatedMetrics)->mapWithKeys(fn($m) => [
                    $m['type'] => $m['context']
                ])
            );

        DeviceLatestData::updateOrCreate(
            [
                'device_type' => get_class($this->device),
                'device_id' => $this->device->id
            ],
            [
                'context' => $latest->all(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> broadcastData
     * <Function> Broadcast the newly saved data to listeners.
     *
     * @return self
     */
    private function broadcastData(): self
    {
        $allData = array_merge($this->dataCollection->toArray(), $this->calculatedMetrics);

        NewElectricMeterData::broadcast($this->device, collect($allData));

        return $this;
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> calculatedBalance
     * <Function> Prepare metrics for calculating load balance.
     *
     * @return array
     */
    private function calculatedBalance(): array
    {
        $now = now()->toISOString();

        $types = [
            'current_unbalance' => self::CURRENT,
            'voltage_unbalance' => self::VOLTAGE_PHASE,
        ];

        return array_map(fn($type, $const) => [
            'device_id'   => $this->device->id,
            'type'        => $type,
            'context'     => [
                'value' => $this->calculateLoadBalance($const),
                'unit'  => '%'
            ],
            'received_at' => $now
        ], array_keys($types), $types);
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> dispatchEnergyMonitor
     * <Function> Filter and dispatch the MonitorEnergyConsumption job.
     *
     * @param \Illuminate\Support\Collection $data
     * @return void
     */
    private function dispatchEnergyMonitor(): void
    {
        $energyData = $this->dataCollection->whereIn('type', self::ENERGY_TYPES);

        if ($energyData->isNotEmpty()) {
            MonitorEnergyConsumption::dispatch($energyData, $this->device->id)
                ->onConnection('gcloud');
        }
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> calculateLoadBalance
     * <Function> Calculate load balance percentage for given types.
     *
     * @param array $types
     * @return float
     */
    public function calculateLoadBalance(array $types): float
    {
        $values = $this->dataCollection
            ->filter(fn($d) => in_array($d->type, $types))
            ->pluck('context.value')
            ->toArray();

        if (empty($values)) return 0;

        $avg = array_sum($values) / count($values);

        if ($avg == 0) return 0;

        $maxDev = max(array_map(fn($v) => abs($v - $avg), $values));

        return round(($maxDev / $avg) * 100, 2);
    }
}
