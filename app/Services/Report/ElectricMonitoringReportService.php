<?php

namespace App\Services\Report;

use App\Jobs\Report\CacheElectricReportGraphSummary;
use App\Models\{ElectricityRate, HourlyConsumption, LorawanDevice};
use App\Services\Utils\Downsampler;
use Carbon\{Carbon, CarbonPeriod};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\{Collection, Str};
use Illuminate\Support\Facades\Cache;

class ElectricMonitoringReportService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // EMISSION_FACTOR                (1.0)  The emission factor used to
    //                                       calculate carbon footprint from
    //                                       electricity consumption.
    // TARGET_KWH_MONTH               (2.0)  The target kWh per month for carbon
    //                                       footprint calculations.
    // DOWNSAMPLE_THRESHOLD           (3.0) The threshold for downsampling time
    //                                       series data.
    // getReportData                  (4.0)  Fetches and prepares the data
    //                                       required for the electric
    //                                       monitoring report.
    // getGeneratedReport             (5.0)  Dispatches a job to generate the
    //                                       electric monitoring report PDF.
    // loadSummaryData                (6.0)  Loads summary data for the electric
    //                                       monitoring report.
    // loadTotalConsumptionPerMeter   (7.0)  Loads total consumption per meter
    //                                       for the electric monitoring report.
    // loadCarbonFootprintSummary     (8.0)  Loads carbon footprint summary for
    //                                       the electric monitoring report.
    // loadConsumptionReportData      (9.0) Loads consumption report data for
    //                                       the electric monitoring report.
    // loadMonthlyConsumptionData     (11.0) Loads monthly consumption data for
    //                                       the electric monitoring report.
    // loadHourlyConsumptionData      (12.0) Loads hourly consumption data for
    //                                       the electric monitoring report.
    // loadDailyEnergyCost            (13.0) Loads daily energy cost data for
    //                                       the electric monitoring report.
    // loadCarbonFootprintData        (14.0) Loads carbon footprint data for
    //                                       the electric monitoring report.
    // calculateCarbonSummary         (15.0) Calculates the carbon summary from
    //                                       grouped consumption data.
    // calculateCarbonWeeklyTrend     (16.0) Calculates the weekly carbon trend
    //                                       from grouped consumption data.
    // sumKwhFromGrouped              (17.0) Sums kWh from grouped consumption
    //                                       data within a specified date range.
    // executeConsumptionQuery        (18.0) Executes the SQL query to fetch
    //                                       consumption data for the specified
    //                                       meters and date range.
    // downsampleTimeSeries           (19.0) Downsamples time series data to a
    //                                       manageable size for reporting.
    // timeRangeParser                (20.0) Parses the time range from the
    //                                       input data and converts them to UTC.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> EMISSION_FACTOR
     * <Function> The emission factor used to calculate carbon footprint from electricity consumption.
     *
     * @var float
     */
    private const EMISSION_FACTOR = 0.7181;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> TARGET_KWH_MONTH
     * <Function> The target kWh per month for carbon footprint calculations.
     *
     * @var int
     */
    private const TARGET_KWH_MONTH = 10000;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> DOWNSAMPLE_THRESHOLD
     * <Function> The threshold for downsampling time series data.
     *
     * @var int
     */
    private const DOWNSAMPLE_THRESHOLD = 1000;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getReportData
     * <Function> Fetches and prepares the data required for the electric monitoring report.
     *
     * @param array $validated
     * @param string $jobId
     * @return array
     */
    public function getReportData(array $validated, string $jobId): array
    {
        $meters = LorawanDevice::whereIn('id', $validated['meters'])
            ->select('id', 'name', 'dev_eui')
            ->get();

        return [
            'jobId' => $jobId,
            'meters' => $meters,
            'filters' => ['start' => $validated['start'], 'end' => $validated['end'], 'meters' => $validated['meters']],
            'preloadedData' => [
                'summary' => Cache::pull('summary_report_' . $jobId),
                'total_consumption' => Cache::pull('total_consumption_report_' . $jobId),
                'consumption_report' => Cache::pull('consumption_report_' . $jobId),
                'monthly_consumption' => Cache::pull('monthly_consumption_report_' . $jobId),
                'hourly_consumption' => Cache::pull('hourly_consumption_report_' . $jobId),
                'energy_cost' => Cache::pull('daily_energy_cost_report_' . $jobId),
                'carbon_footprint' => Cache::pull('carbon_footprint_report_' . $jobId),
            ],
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getGeneratedReport
     * <Function> Dispatches a job to generate the electric monitoring report PDF.
     *
     * @param array $validated

     */
    public function getGeneratedReport(array $validated): JsonResponse
    {
        $jobId = Str::uuid()->toString();
        $timestamp = time();
        $userId = auth()->id();

        $filename = "electric-report-{$validated['start']}-to-{$validated['end']}-{$timestamp}.pdf";

        CacheElectricReportGraphSummary::dispatch($validated, $jobId, $userId, $filename)->onConnection('gcloud');

        return response()->json([
            'job_id' => $jobId,
            'filename' => $filename
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> loadSummaryData
     * <Function> Loads summary data for the electric monitoring report.
     *
     * @param mixed $data
     * @return array
     */
    public function loadSummaryData($data): array
    {
        return [
            'total_consumption' => collect($this->loadTotalConsumptionPerMeter($data))->sum('value'),
            'total_cost' => $this->loadDailyEnergyCost($data)['monthly_breakdown']->sum('total_cost'),
            'carbon_footprint' => $this->loadCarbonFootprintSummary($data)['total_co2_emissions'],
        ];
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> loadTotalConsumptionPerMeter
     * <Function> Loads total consumption per meter for the electric monitoring report.
     *
     * @param array $data
     * @return array
     */
    public function loadTotalConsumptionPerMeter($data): array
    {
        [$start, $end] = $this->parseTimeRange($data);
        $results = collect($this->executeConsumptionQuery($data['meters'],  $end, $start));

        return $results->groupBy('device_id')
            ->map(fn($items) => [
                'name' => $items->first()->name,
                'value' => round($items->sum('adjusted_current'), 2)
            ])
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> loadCarbonFootprintSummary
     * <Function> Loads carbon footprint summary for the electric monitoring report.
     *
     * @param array $data
     * @return array
     */
    public function loadCarbonFootprintSummary($data): array
    {
        [$start, $end] = $this->parseTimeRange($data);
        $totalKwh = collect($this->executeConsumptionQuery($data['meters'], $end, $start))
            ->sum('adjusted_current');

        return [
            'total_co2_emissions' => round(($totalKwh * self::EMISSION_FACTOR) / 1000, 2),
            'total_kwh' => round($totalKwh, 2),
            'carbon_intensity' => self::EMISSION_FACTOR,
            'unit' => 'tonnes CO2'
        ];
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> loadConsumptionReportData
     * <Function> Loads consumption report data for the electric monitoring report.
     *
     * @param array $data
     * @return array
     */
    public function loadConsumptionReportData($data): array
    {
        [$start, $end] = $this->parseTimeRange($data);
        $days = max($start->diffInDays($end), 1);

        $records = HourlyConsumption::whereIn('device_id', $data['meters'])
            ->whereBetween('date', [$start, $end])
            ->where('consumption->type', 'positive_active_energy')
            ->orderBy('device_id')
            ->orderBy('date')
            ->get();

        $total = 0;
        $previous = [];

        foreach ($records as $record) {
            $value = $record->consumption['value'] ?? 0;
            $total += $value - ($previous[$record->device_id] ?? 0);
            $previous[$record->device_id] = $value;
        }

        $current = round($total, 2);

        return [
            'consumption' => ['current' => $current],
            'avg_daily' => ['current' => $days > 0 ? round($current / $days, 2) : 0]
        ];
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> loadMonthlyConsumptionData
     * <Function> Loads monthly consumption data for the electric monitoring report.
     *s
     * @param array $data
     * @return \Illuminate\Support\Collection
     */
    public function loadMonthlyConsumptionData($data): Collection
    {
        [$start, $end] = $this->parseTimeRange($data);
        $results = collect($this->executeConsumptionQuery($data['meters'],  $end, $start));

        if ($results->isEmpty()) return collect();

        return collect([[
            'name' => 'Total Consumption',
            'data' => $results->groupBy(fn($r) => Carbon::parse($r->date)->timezone('Asia/Manila')->format('Y-m'))
                ->map(fn($monthRecords, $month) => [$month, round($monthRecords->sum('adjusted_current'), 2)])
                ->values()
                ->toArray()
        ]]);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> loadHourlyConsumptionData
     * <Function> Loads hourly consumption data for the electric monitoring report.
     *
     * @param array $data
     * @return \Illuminate\Support\Collection
     */
    public function loadHourlyConsumptionData($data): Collection
    {
        [$start, $end] = $this->parseTimeRange($data);
        $results = collect($this->executeConsumptionQuery($data['meters'],  $end, $start));

        return $results->groupBy('device_id')
            ->map(fn($records) => [
                'name' => $records->first()->name,
                'data' => $this->downsampleTimeSeries(
                    $records->map(fn($r) => [Carbon::parse($r->date)->timezone('Asia/Manila')->format('Y-m-d H:i'), $r->adjusted_current])->toArray()
                )
            ])
            ->values();
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> loadDailyEnergyCost
     * <Function> Loads daily energy cost data for the electric monitoring report.
     *
     * @param array $data
     * @return array
     */
    public function loadDailyEnergyCost($data): array
    {
        [$start, $end] = $this->parseTimeRange($data);
        $startManila = $start->copy()->tz('Asia/Manila');
        $weeks = collect(CarbonPeriod::create($startManila, '1 week', $end->copy()->tz('Asia/Manila')))->map->format('Y-m-d');

        $records = collect($this->executeConsumptionQuery($data['meters'],  $end, $start))
            ->map(fn($r) => (object)[
                'device_id' => $r->device_id,
                'name' => $r->name,
                'date' => Carbon::parse($r->date)->setTimezone('Asia/Manila')->toDateTimeString(),
                'adjusted_current' => $r->adjusted_current
            ]);

        $monthlyBreakdown = $records->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'))
            ->sortKeys()
            ->map(function ($monthRecords, $yearMonth) {
                $totalConsumption = $monthRecords->sum('adjusted_current');
                $rate = ElectricityRate::effectiveRate(Carbon::parse($yearMonth . '-01')) ?? 0;
                return [
                    'month' => Carbon::parse($yearMonth . '-01')->format('M Y'),
                    'total_cost' => round($totalConsumption * $rate, 2),
                    'consumption' => round($totalConsumption, 2),
                    'rate' => $rate
                ];
            })->values();

        $series = $records->groupBy('device_id')->map(function ($records) use ($weeks, $startManila) {
            $usageByWeek = $records->groupBy(function ($r) use ($startManila) {
                return $startManila->copy()->addWeeks($startManila->diffInWeeks(Carbon::parse($r->date)))->format('Y-m-d');
            })->map(fn($weekRecords) => $weekRecords->sum(
                fn($r) => $r->adjusted_current * (ElectricityRate::effectiveRate(Carbon::parse($r->date)) ?? 0)
            ));

            return [
                'name' => $records->first()->name,
                'data' => $weeks->map(fn($date) => [$date, round($usageByWeek->get($date, 0), 2)])->values()
            ];
        })->values();

        return [
            'latest_rate' => ElectricityRate::query()->latestRate()->first(['rate_per_kwh']),
            'monthly_breakdown' => $monthlyBreakdown,
            'series' => $series
        ];
    }


    /**
     * <Layer number> (14.0)
     *
     * <Processing name> loadCarbonFootprintData
     * <Function> Loads carbon footprint data for the electric monitoring report.
     *
     * @param array $data
     * @return array
     */
    public function loadCarbonFootprintData($data): array
    {
        [$start, $end] = $this->parseTimeRange($data);
        $days = max($start->diffInDays($end), 1);

        $records = collect($this->executeConsumptionQuery($data['meters'], $end, $start));

        $grouped = $records->groupBy(fn($r) => Carbon::parse($r->date)->timezone('Asia/Manila')->toDateString());

        $carbonSummary = $this->calculateCarbonSummary($grouped, $start, $end, $days);

        return [
            'summary' => $carbonSummary['summary'],
            'monthly_breakdown' => $carbonSummary['monthly_breakdown'],
            'trend' => [['name' => 'CO2 Emissions', 'data' => $this->calculateCarbonWeeklyTrend($grouped, $start, $end)]],
        ];
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> calculateCarbonSummary
     * <Function> Calculates the carbon summary from grouped consumption data.
     *
     * @param mixed $grouped
     * @param Carbon $start
     * @param Carbon $end
     * @param int $duration
     * @return array
     */
    private function calculateCarbonSummary($grouped, $start, $end, $duration): array
    {
        $startManila = $start->copy()->tz('Asia/Manila');
        $endManila = $end->copy()->tz('Asia/Manila');

        $totalKwh = $this->sumKwhFromGrouped($grouped, $startManila, $endManila);
        $prevKwh = $this->sumKwhFromGrouped($grouped, $startManila->copy()->subDays($duration), $startManila->copy()->subSecond());

        $currentEmissions = ($totalKwh * self::EMISSION_FACTOR) / 1000;
        $prevEmissions = ($prevKwh * self::EMISSION_FACTOR) / 1000;

        $months = (int)floor($startManila->copy()->startOfMonth()->diffInMonths($endManila)) + 1;
        $monthlyData = collect(range(0, max(0, $months - 1)))->map(function ($i) use ($startManila, $grouped) {
            $month = $startManila->copy()->startOfMonth()->addMonths($i);
            $monthKwh = $this->sumKwhFromGrouped($grouped, $month->copy()->startOfMonth(), $month->copy()->endOfMonth());
            $emissions = $monthKwh * self::EMISSION_FACTOR;
            $excessKwh = max(0, $monthKwh - self::TARGET_KWH_MONTH);

            return [
                'month' => $month->format('M Y'),
                'consumption' => round($monthKwh, 2),
                'co2_emissions' => round($emissions, 2),
                'excess_emissions' => round(max(0, $emissions - self::TARGET_KWH_MONTH * self::EMISSION_FACTOR), 2),
                'potential_savings' => round($excessKwh * (ElectricityRate::effectiveRate($month) ?? 0), 2),
                'daily_average' => round($emissions / $month->daysInMonth, 2),
            ];
        })->toArray();

        return [
            'summary' => [
                'total_co2_emissions' => round($currentEmissions, 2),
                'carbon_intensity' => self::EMISSION_FACTOR,
                'previous_period' => round($prevEmissions, 2),
                'reduction_percent' => $prevEmissions > 0 ? round((($currentEmissions - $prevEmissions) / $prevEmissions) * 100, 1) : 0,
            ],
            'monthly_breakdown' => $monthlyData,
        ];
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> calculateCarbonWeeklyTrend
     * <Function> Calculates the weekly carbon trend from grouped consumption data.
     *
     * @param mixed $grouped
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    private function calculateCarbonWeeklyTrend($grouped, $start, $end): array
    {
        $startWeek = $start->copy()->tz('Asia/Manila');
        $weeks = (int)floor($startWeek->diffInWeeks($end->copy()->tz('Asia/Manila'))) + 1;

        return collect(range(0, max(0, $weeks - 1)))->map(function ($i) use ($startWeek, $grouped) {
            $week = $startWeek->copy()->addWeeks($i);
            $weekKwh = $this->sumKwhFromGrouped($grouped, $week, $week->copy()->addDays(6)->endOfDay());
            return [$week->format('M d, Y'), round(($weekKwh * self::EMISSION_FACTOR) / 1000, 2)];
        })->toArray();
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> sumKwhFromGrouped
     * <Function> Sums kWh from grouped consumption data within a specified date range.
     *
     * @param mixed $grouped
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    private function sumKwhFromGrouped($grouped, $start, $end): mixed
    {
        return $grouped->filter(fn($items, $date) => $date >= $start->toDateString() && $date <= $end->toDateString())
            ->flatten()
            ->sum('adjusted_current');
    }


    /**
     * <Layer number> (18.0)
     *
     * <Processing name> executeConsumptionQuery
     * <Function> Executes the SQL query to fetch consumption data for the specified meters and date range.
     *            Results are cached to optimize multiple calls with identical parameters.
     *
     * @param array $meterIds
     * @param mixed $beforeStart
     * @param mixed $end
     * @param mixed $actualStart
     */
    private function executeConsumptionQuery($meterIds, $end, $actualStart): array
    {
        $cacheKey = 'consumption_query_' . md5(
            json_encode($meterIds) .
                $end->toDateTimeString() .
                $actualStart->toDateTimeString()
        );

        return Cache::remember($cacheKey, 3600, function () use ($meterIds, $end, $actualStart) {
            return HourlyConsumption::with('device:id,name')
                ->whereIn('device_id', $meterIds)
                ->whereBetween('date', [$actualStart, $end])
                ->where('consumption->type', 'positive_active_energy')
                ->get()
                ->map(function ($record) {
                    return (object)[
                        'device_id' => $record->device_id,
                        'name' => $record->device->name ?? null,
                        'date' => $record->date,
                        'adjusted_current' => $record->consumption['value'] ?? 0,
                        'consumption' => $record->consumption,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> downsampleTimeSeries
     * <Function> Downsamples time series data to a manageable size for reporting.
     *
     * @param array $data
     * @return array
     */
    private function downsampleTimeSeries(array $data): array
    {
        if (empty($data)) return $data;

        $numericData = array_map(fn($p) => [Carbon::parse($p[0], 'Asia/Manila')->timestamp * 1000, $p[1]], $data);
        $sampled = Downsampler::sample($numericData, self::DOWNSAMPLE_THRESHOLD);

        return array_map(fn($p) => [
            Carbon::createFromTimestampMs($p[0], 'Asia/Manila')->format('Y-m-d H:i'),
            $p[1]
        ], $sampled);
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> parseTimeRange
     * <Function> Parses the start and end dates from the input data and converts them to UTC.
     *
     * @param array $data
     * @return array
     */
    private function parseTimeRange($data): array
    {
        return [
            Carbon::parse($data['start'], 'Asia/Manila')->startOfDay()->utc(),
            Carbon::parse($data['end'], 'Asia/Manila')->endOfDay()->utc()
        ];
    }
}
