<?php

namespace App\Jobs\Lorawan\ElectricMeter;

use App\Models\HourlyConsumption;
use App\Models\LorawanDevice;
use App\Models\MonthlyConsumption;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Attributes\WithoutRelations;
use Throwable;

class MonitorEnergyConsumption implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $device                      (1.0) Electric meter device holder variable.
    // __construct                  (2.0) Initialize job with device data and
    //                                    load relationships.
    // handle                       (3.0) Execute consumption calculation
    //                                     workflow.
    // failed                       (4.0) Log job failure details.
    // isElectricMeter              (5.0) Verify device is a supported electric
    //                                    meter.
    // updateHourlyConsumption      (6.0) Calculate and store hourly energy
    //                                    consumption.
    // getPreviousHourConsumption   (7.0) Retrieve previous hour's consumption
    //                                    records.
    // updateCurrentHourConsumption (8.0) Store current hour consumption with
    //                                    calculated difference.
    // updateMonthlyConsumption     (9.0) Aggregate and store monthly floor
    //                                    consumption.

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $device
     * <Function> Electric meter device holder variable.
     *
     * @var \App\Models\LorawanDevice
     */
    private $device;


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Initialize job with device data and load relationships.
     *
     * @param Collection $data
     * @param mixed $device
     * @return void
     */
    public function __construct(
        #[WithoutRelations]
        private Collection $data,
        private string $deviceId
    ) {}

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> handle
     * <Function> Execute consumption calculation workflow.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->device = LorawanDevice::with('room.floor')->find($this->deviceId);

        if (!$this->isElectricMeter()) {
            return;
        }

        DB::transaction(function () {
            $this->updateHourlyConsumption();

            if ($this->data->pluck('type')->contains('positive_active_energy')) {
                $this->updateMonthlyConsumption();
            }
        });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> failed
     * <Function> Log job failure details.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error('Job Failed', [
            'task' => __CLASS__,
            'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> isElectricMeter
     * <Function> Verify device is a supported electric meter.
     *
     * @return bool
     */
    private function isElectricMeter(): bool
    {
        return $this->device && (
            $this->device->model === 'ADW300' ||
            in_array($this->device->context['slave_model'] ?? null, ['ADW300', 'EPM5500P'])
        );
    }


    private function updateHourlyConsumption(): self
    {
        $record = $this->data->sortBy('received_at')->first();
        $timestamp = Carbon::parse($record->received_at)->startOfHour()->addHour();
        $types = $this->data->pluck('type')->unique();

        $previous = HourlyConsumption::where('device_id', $this->device->id)
            ->where('date', '<', $timestamp)
            ->whereIn('consumption->type', $types)
            ->get()
            ->groupBy('consumption.type')
            ->map(fn($records) => $records->sortByDesc('date')->first());

        foreach ($this->data as $reading) {
            $this->storeReading($timestamp, $reading, $previous->get($reading['type']));
        }

        return $this;
    }

    private function storeReading($timestamp, $reading, $prev): void
    {
        $current = $reading['context']['value'];
        $type = $reading['type'];

        $prevCumulative = $prev->consumption['cumulative'] ?? $prev->consumption['current'] ?? $current;
        $prevDate = $prev ? Carbon::parse($prev->date) : null;
        $hasGap = $prevDate && $prevDate->copy()->addHour()->lt($timestamp);
        $consumption = $prev ? max(0, $current - $prevCumulative) : 0;

        if ($hasGap) {
            $prev->update(['consumption->data_status' => 'gap_start']);
        }

        $data = [
            'consumption' => [
                'type' => $type,
                'cumulative' => round($current, 2),
                'value' => round($consumption, 2),
                'data_status' => $hasGap ? 'gap_end' : 'complete',
                'hasGap' => $hasGap,
            ],
            'floor_name' => $this->device->room->floor->name,
        ];

        if ($hasGap) {
            $data['consumption']['gap_info'] = [
                'gap_duration_hours' => $prevDate->diffInHours($timestamp) - 1,
                'gap_start' => $prevDate->copy()->addHour()->format('Y-m-d H:i:s'),
                'gap_end' => $timestamp->format('Y-m-d H:i:s'),
            ];
        }

        HourlyConsumption::updateOrCreate(
            [
                'device_id' => $this->device->id,
                'date' => $timestamp->format('Y-m-d H:i:s'),
                'consumption->type' => $type,
            ],
            $data
        );
    }


    /**
     * <Layer number> (9.0)
     *
     * <Processing name> updateMonthlyConsumption
     * <Function> Aggregate and store monthly floor consumption.
     *
     * @return self
     */
    protected function updateMonthlyConsumption(): self
    {
        $start = now()->setTimeZone('Asia/Manila')->startOfMonth();
        $currentMonthStart = $start->copy()->utc();
        $end = now()->setTimeZone('Asia/Manila')->endOfMonth();
        $currentMonthEnd = $end->copy()->utc();
        $currentMonth = $currentMonthStart->copy()->setTimeZone('Asia/Manila')->format('Y-m-d');

        $records = HourlyConsumption::whereBetween('date', [$currentMonthStart, $currentMonthEnd])
            ->where('floor_name', $this->device->room->floor->name)
            ->where('consumption->type', 'positive_active_energy')
            ->get();

        $confirmedConsumption = 0.0;
        $unattributedConsumption = 0.0;

        foreach ($records as $record) {
            $consumption = $record->consumption;
            if ($consumption['hasGap'] === true) {
                $unattributedConsumption += $consumption['value'];
            } else {
                $confirmedConsumption += $consumption['value'];
            }
        }

        $totalConsumption = $confirmedConsumption + $unattributedConsumption;

        MonthlyConsumption::updateOrCreate(
            [
                'date' => $currentMonth,
                'floor_name' => optional($this->device->room->floor)->name ?? 'not set'
            ],
            [
                'consumption' => $totalConsumption,
                'confirmed_consumption' => $confirmedConsumption,
                'unattributed_consumption' => $unattributedConsumption,
                'has_unattributed' => $unattributedConsumption > 0,
                'updated_at' => now()
            ]
        );

        return $this;
    }
}
