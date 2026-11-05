<?php

namespace App\Services\Admin;

use App\Models\ClimateDataRecord;
use App\Models\LorawanDeviceData;
use Illuminate\Support\Carbon;

class ClimateAnalyticsService
{
    /**************************************************************************/
    /* Processing Hierarchy */
    /**************************************************************************/
    // TIMEZONE                (1.0) The timezone used for hour labels and
    //                               buckets.
    // DEFAULT_HOURS           (2.0) The window used when no filter is given.
    // getSetpointAndActual    (3.0) Get merged setpoint and actual
    //                               temperature chart data.
    // getONandOFFPatterns     (4.0) Get ON & OFF pattern chart data.
    // getFanSpeed             (5.0) Get fan speed chart data.
    // getTemperature          (6.0) Get temperature chart data.
    // getSetpoint             (7.0) Get setpoint chart data.
    // getDataByType           (8.0) Get chart data for a single data type.
    // parseTimeRange          (9.0) Resolve the requested window.
    // resolutionFor           (10.0) Determine the bucket resolution of a
    //                               window.
    // buildBuckets            (11.0) Build the bucket list of a window.
    // floorToResolution       (12.0) Floor a timestamp to its bucket.
    // advance                 (13.0) Move to the next bucket.
    // bucketKey               (14.0) Compute the bucket key of a timestamp.
    // resolveWindow           (15.0) Resolve the window, its resolution and
    //                               its bucket list.
    // normalizePower          (16.0) Normalize a stored power reading.
    // normalizeFanSpeed       (17.0) Normalize a stored fan reading.
    // resolveReadingsWindow   (18.0) Resolve the raw-reading window.
    // getReadings             (19.0) Return thermostat readings for the stat
    //                               cards, with an hourly chart fallback.
    // normalizeReadingFan     (20.0) Normalize a raw fan reading for stats.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> TIMEZONE
     * <Function> The timezone used for hour labels and buckets.
     *
     * @var string
     */
    private const TIMEZONE = 'Asia/Manila';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> DEFAULT_HOURS
     * <Function> The window used when no filter is given.
     *
     * @var int
     */
    public const DEFAULT_HOURS = 24;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getSetpointAndActual
     * <Function> Get merged setpoint and actual temperature chart data.
     *
     * @param  mixed  $time  Window in hours, or an array with start/end dates.
     * @return array<string, mixed>
     */
    public function getSetpointAndActual(mixed $time, string $room, string $roomArea): array
    {
        $setpoint = $this->getDataByType($time, $room, $roomArea, 'current_setpoint', [
            'label' => 'Setpoint',
            'color' => '#F97316',
        ]);

        $actual = $this->getDataByType($time, $room, $roomArea, 'temperature', [
            'label' => 'Actual',
            'color' => '#0D9488',
        ]);

        return [
            'labels' => $setpoint['labels'],
            'datasets' => array_merge($setpoint['datasets'], $actual['datasets']),
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getONandOFFPatterns
     * <Function> Get ON & OFF pattern chart data.
     *
     * @return array<string, mixed>
     */
    public function getONandOFFPatterns(mixed $time, string $room, string $roomArea): array
    {
        return $this->getDataByType($time, $room, $roomArea, 'slave_power', [
            'label' => 'Power',
            'color' => '#6366F1',
            'stepped' => true,
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getFanSpeed
     * <Function> Get fan speed chart data.
     *
     * @return array<string, mixed>
     */
    public function getFanSpeed(mixed $time, string $room, string $roomArea): array
    {
        return $this->getDataByType($time, $room, $roomArea, 'fan_speed', [
            'label' => 'Fan Speed',
            'color' => '#627254',
            'stepped' => true,
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getTemperature
     * <Function> Get temperature chart data.
     *
     * @return array<string, mixed>
     */
    public function getTemperature(mixed $time, string $room, string $roomArea): array
    {
        return $this->getDataByType($time, $room, $roomArea, 'temperature', [
            'label' => 'Temperature',
            'color' => '#0D9488',
        ]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getSetpoint
     * <Function> Get setpoint chart data.
     *
     * @return array<string, mixed>
     */
    public function getSetpoint(mixed $time, string $room, string $roomArea): array
    {
        return $this->getDataByType($time, $room, $roomArea, 'current_setpoint', [
            'label' => 'Setpoint',
            'color' => '#F97316',
        ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getDataByType
     * <Function> Get chart data for a single data type over the requested
     *            window.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function getDataByType(mixed $time, string $room, string $roomArea, string $type, array $config): array
    {
        $window = $this->resolveWindow($time);
        $labels = array_map(fn(Carbon $bucket) => $bucket->format('Y-m-d H:i'), $window['buckets']);

        $records = ClimateDataRecord::query()
            ->whereBetween('date', [
                $window['start']->copy()->utc()->format('Y-m-d H:i:s'),
                $window['end']->copy()->utc()->format('Y-m-d H:i:s'),
            ])
            ->where('type', $type)
            ->whereHas('device', fn($q) => $q->where('room_id', $room)->where('room_area_id', $roomArea))
            ->orderBy('date')
            ->get();

        if ($records->isEmpty()) {
            return ['labels' => $labels, 'datasets' => []];
        }

        $indexByKey = array_flip($labels);
        $data = array_fill(0, count($labels), null);

        $groups = $records->groupBy(
            fn($record) => $this->bucketKey(
                Carbon::parse($record->date)->utc()->tz(self::TIMEZONE),
                $window['resolution']
            )
        );

        foreach ($groups as $key => $group) {
            $index = $indexByKey[$key] ?? null;

            if ($index === null) {
                continue;
            }

            $data[$index] = match ($type) {
                'slave_power' => $this->normalizePower((string) $group->last()->value),
                'current_setpoint' => (float) $group->last()->value,
                'fan_speed' => $this->normalizeFanSpeed((string) $group->last()->value),
                default => round($group->avg(fn($record) => (float) $record->value), 2),
            };
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => $config['label'],
                'borderColor' => $config['color'],
                'backgroundColor' => $config['color'],
                'data' => $data,
                ...array_filter($config, fn($key) => in_array($key, ['stepped'], true), ARRAY_FILTER_USE_KEY),
            ]],
        ];
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> parseTimeRange
     * <Function> Resolve the requested window.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function parseTimeRange(mixed $time): array
    {
        $now = Carbon::now(self::TIMEZONE)->startOfHour();

        if (is_array($time)) {
            $start = Carbon::parse($time['start'] ?? '', self::TIMEZONE)->startOfDay();
            $end = Carbon::parse($time['end'] ?? '', self::TIMEZONE)->endOfDay();

            return [$start, $end->greaterThan($now) ? $now : $end];
        }

        $hours = max(1, (int) $time);
        $start = $now->copy()->subHours($hours - 1)->startOfHour();

        return [$start, $now];
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> resolutionFor
     * <Function> Determine the bucket resolution of a window.
     */
    public function resolutionFor(mixed $time): string
    {
        [$start, $end] = $this->parseTimeRange($time);

        $hours = (int) max(1, ceil(abs($end->getTimestamp() - $start->getTimestamp()) / 3600));

        return match (true) {
            $hours <= 24 => 'hour',
            $hours <= 168 => 'six_hour',
            default => 'day',
        };
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> buildBuckets
     * <Function> Build the bucket list of a window.
     *
     * @return list<Carbon>
     */
    private function buildBuckets(Carbon $start, Carbon $end, string $resolution): array
    {
        $buckets = [];
        $cursor = $this->floorToResolution($start, $resolution);

        while ($cursor->lessThanOrEqualTo($end)) {
            $buckets[] = $cursor->copy();
            $cursor = $this->advance($cursor, $resolution);
        }

        return $buckets;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> floorToResolution
     * <Function> Floor a timestamp to the start of its bucket.
     */
    private function floorToResolution(Carbon $timestamp, string $resolution): Carbon
    {
        $timestamp = $timestamp->copy()->timezone(self::TIMEZONE)->second(0);

        return match ($resolution) {
            'hour' => $timestamp->startOfHour(),
            'six_hour' => $timestamp->setTime((int) floor($timestamp->hour / 6) * 6, 0)->startOfHour(),
            default => $timestamp->startOfDay(),
        };
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> advance
     * <Function> Move to the next bucket.
     */
    private function advance(Carbon $timestamp, string $resolution): Carbon
    {
        return match ($resolution) {
            'hour' => $timestamp->copy()->addHour(),
            'six_hour' => $timestamp->copy()->addHours(6),
            default => $timestamp->copy()->addDay(),
        };
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> bucketKey
     * <Function> Compute the bucket key of a timestamp.
     */
    private function bucketKey(Carbon $timestamp, string $resolution): string
    {
        return $this->floorToResolution($timestamp, $resolution)->format('Y-m-d H:i');
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> resolveWindow
     * <Function> Resolve the window, its resolution and its bucket list.
     *
     * @return array{start: Carbon, end: Carbon, resolution: string, buckets: list<Carbon>}
     */
    private function resolveWindow(mixed $time): array
    {
        [$start, $end] = $this->parseTimeRange($time);
        $resolution = $this->resolutionFor($time);

        return [
            'start' => $start,
            'end' => $end,
            'resolution' => $resolution,
            'buckets' => $this->buildBuckets($start, $end, $resolution),
        ];
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> normalizePower
     * <Function> Normalize a stored power reading to 1 (on) or 0 (off).
     */
    private function normalizePower(string $value): int
    {
        return strtolower(trim($value)) === 'on' ? 1 : 0;
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> normalizeFanSpeed
     * <Function> Normalize a stored fan reading to 0-3, or 4 for Auto. The
     *            chart uses 4 so Auto remains visible instead of being cast
     *            to Off.
     */
    private function normalizeFanSpeed(string $value): int
    {
        if (strtolower(trim($value)) === 'auto') {
            return 4;
        }

        return max(0, min(3, (int) $value));
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> resolveReadingsWindow
     * <Function> Resolve the raw-reading window. Unlike the chart window this
     *            includes the current partial hour so the status bar can show
     *            the latest uplink instead of reporting the device offline.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    private function resolveReadingsWindow(mixed $time): array
    {
        $now = Carbon::now(self::TIMEZONE);
        [$start, $end] = $this->parseTimeRange($time);

        if (! is_array($time)) {
            $hours = max(1, (int) $time);

            return [
                'start' => $now->copy()->subHours($hours - 1)->startOfHour(),
                'end' => $now,
            ];
        }

        if ($end->isSameDay($now)) {
            $end = $now;
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> getReadings
     * <Function> Return the thermostat readings for the selected room and
     *            area.
     *
     * @return list<array<string, mixed>>
     */
    public function getReadings(mixed $time, string $room, string $roomArea): array
    {
        $window = $this->resolveReadingsWindow($time);

        $rows = LorawanDeviceData::query()
            ->whereBetween('received_at', [
                $window['start']->copy()->utc(),
                $window['end']->copy()->utc(),
            ])
            ->where('transmission', LorawanDeviceData::TRANSMISSION_UPLINK)
            ->whereIn('type', [
                'temperature',
                'current_setpoint',
                'slave_power',
                'fan_speed',
                'mode',
                'setpoint_source',
            ])
            ->whereHas(
                'device',
                fn($query) => $query
                    ->where('room_id', $room)
                    ->where('room_area_id', $roomArea)
                    ->deviceType('thermostat')
            )
            ->orderBy('received_at')
            ->get(['received_at', 'type', 'context']);

        $readings = [];
        $rawHours = [];

        foreach ($rows as $row) {
            $value = $row->context['value'] ?? null;

            if ($value === null) {
                continue;
            }

            $receivedAt = $row->received_at->copy()->utc();
            $key = $receivedAt->format('Y-m-d H:i');

            $rawHours[$receivedAt->format('Y-m-d H')] = true;

            $readings[$key] ??= [
                'timestamp' => $receivedAt->toIso8601String(),
                'temperature' => [],
                'setpoint' => [],
                'power' => null,
                'fan_speed' => null,
                'mode' => null,
                'setpoint_source' => null,
            ];

            switch ($row->type) {
                case 'temperature':
                    $readings[$key]['temperature'][] = (float) $value;
                    break;

                case 'current_setpoint':
                    $readings[$key]['setpoint'][] = (float) $value;
                    break;

                case 'slave_power':
                    $readings[$key]['power'] = strtolower(trim((string) $value)) === 'on' ? 'on' : 'off';
                    break;

                case 'fan_speed':
                    $readings[$key]['fan_speed'] = $this->normalizeReadingFan($value);
                    break;

                case 'mode':
                    $readings[$key]['mode'] = (string) $value;
                    break;

                case 'setpoint_source':
                    $readings[$key]['setpoint_source'] = (string) $value;
                    break;
            }
        }

        // Raw uplinks are not retained as long as the chart records. Fall back
        // to the hourly chart data for hours without raw readings so old
        // ranges still produce stats instead of showing hyphens.
        $chartRows = ClimateDataRecord::query()
            ->whereBetween('date', [
                $window['start']->copy()->utc(),
                $window['end']->copy()->utc(),
            ])
            ->whereIn('type', [
                'temperature',
                'current_setpoint',
                'slave_power',
                'fan_speed',
            ])
            ->whereHas(
                'device',
                fn($query) => $query
                    ->where('room_id', $room)
                    ->where('room_area_id', $roomArea)
                    ->deviceType('thermostat')
            )
            ->orderBy('date')
            ->get(['date', 'type', 'value']);

        $chartGroups = $chartRows->groupBy(
            fn($row) => Carbon::parse($row->date)->utc()->format('Y-m-d H')
        );

        foreach ($chartGroups as $hour => $chartReadings) {
            if (isset($rawHours[$hour])) {
                continue;
            }

            $first = $chartReadings->first();
            $receivedAt = Carbon::parse($first->date)->utc();
            $key = $receivedAt->format('Y-m-d H:i');

            $readings[$key] = [
                'timestamp' => $receivedAt->toIso8601String(),
                'temperature' => [],
                'setpoint' => [],
                'power' => null,
                'fan_speed' => null,
                'mode' => null,
                'setpoint_source' => null,
            ];

            foreach ($chartReadings as $row) {
                $value = $row->value;

                if ($value === null || $value === '') {
                    continue;
                }

                switch ($row->type) {
                    case 'temperature':
                        $readings[$key]['temperature'][] = (float) $value;
                        break;

                    case 'current_setpoint':
                        $readings[$key]['setpoint'][] = (float) $value;
                        break;

                    case 'slave_power':
                        $readings[$key]['power'] = strtolower(trim((string) $value)) === 'on' ? 'on' : 'off';
                        break;

                    case 'fan_speed':
                        $readings[$key]['fan_speed'] = $this->normalizeReadingFan($value);
                        break;
                }
            }
        }

        return array_values(array_map(function (array $reading): array {
            $temperatures = $reading['temperature'];
            $setpoints = $reading['setpoint'];

            $reading['temperature'] = $temperatures === [] ? null : round(array_sum($temperatures) / count($temperatures), 2);
            $reading['setpoint'] = $setpoints === [] ? null : round(array_sum($setpoints) / count($setpoints), 2);

            return $reading;
        }, $readings));
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> normalizeReadingFan
     * <Function> Normalize a raw fan reading for the stat cards. Auto and the
     *            legacy level 4 stay Auto; the remaining values clamp to Off,
     *            Low, Medium or High.
     */
    private function normalizeReadingFan(mixed $value): string|int
    {
        $fan = strtolower(trim((string) $value));

        if ($fan === 'auto' || (is_numeric($fan) && (int) $fan >= 4)) {
            return 'auto';
        }

        return max(0, min(3, (int) $fan));
    }
}
