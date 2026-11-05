<?php

namespace App\Jobs\Lorawan\AISensor;

use App\Events\Lorawan\AISensor\OccupancyUpdated;
use App\Models\{DailyOccupancy, DailyPeopleFlow, DeviceLatestData};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;
use Throwable;

class ProcessOccupancyCount implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $occupancy           (1.0) The updated daily occupancy record.
    // __construct          (2.0) Create a new job instance.
    // handle               (3.0) Execute the job logic.
    // failed               (4.0) Handle a job failure.
    // accumulatedInChange  (5.0) Count change based on accumulated in.
    // accumulatedOutChange (6.0) Count change based on accumulated out.
    // updateOccupancy      (7.0) Update daily occupancy record based on change.
    // broadcastEvent       (8.0) Broadcast new update for occupancy data.

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $occupancy
     * <Function> The updated daily occupancy record.
     *
     * @var \App\Models\DailyOccupancy
     */
    private $occupancy;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance.
     *
     * @param \App\Models\DeviceLatestData $data
     */
    public function __construct(protected DeviceLatestData $data) {}

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> handle
     * <Function> Execute the job logic.
     *
     * @return void
     */
    public function handle(): void
    {
        $context = $this->data->context;

        // Check if accumulated_in changed
        if (isset($context['accumulated_in'])) {
            $this->updateOccupancy($this->accumulatedInChange());
        }

        // Check if accumulated_out changed
        if (isset($context['accumulated_out'])) {
            $this->updateOccupancy($this->accumulatedOutChange());
        }

        $this->broadcastEvent();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        // Error handling
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
     * <Processing name> accumulatedInChange
     * <Function> Count change based on accumulated in value.
     *
     * @return int
     */
    private function accumulatedInChange(): int
    {
        $change = 0;

        DB::transaction(function () use (&$change) {
            $date = Carbon::parse($this->data->created_at)->setTimezone('Asia/Manila');

            $previous = DailyPeopleFlow::where('device_id', $this->data->device_id)
                ->whereDate('date', $date)
                ->lockForUpdate()
                ->first();

            [$newIn, $currentIn, $currentOut] = [
                $this->data->context['accumulated_in']['value'],
                @$previous->context['accumulated_in'],
                @$previous->context['accumulated_out'],
            ];

            if ($previous) {
                $previous->update([
                    'context' => [
                        'accumulated_in'  => $newIn,
                        'accumulated_out' => $currentOut,
                    ]
                ]);
                $change = $newIn - $currentIn;
            } else {
                DailyPeopleFlow::create([
                    'device_id' => $this->data->device_id,
                    'date'      => $date,
                    'context'   => [
                        'accumulated_in'  => $newIn,
                        'accumulated_out' => 0,
                    ]
                ]);
            }
        });

        return $change;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> accumulatedOutChange
     * <Function> Count change based on accumulated out value.
     *
     * Updates or creates people flow and computes difference in out-count.
     *
     * @return int
     */
    private function accumulatedOutChange(): int
    {
        $change = 0;

        DB::transaction(function () use (&$change) {
            $date = Carbon::parse($this->data->created_at)->setTimezone('Asia/Manila');

            $previous = DailyPeopleFlow::where('device_id', $this->data->device_id)
                ->whereDate('date', $date)
                ->lockForUpdate()
                ->first();

            [$newOut, $currentIn, $currentOut] = [
                $this->data->context['accumulated_out']['value'],
                @$previous->context['accumulated_in'],
                @$previous->context['accumulated_out'],
            ];

            if ($previous) {
                $previous->update([
                    'context' => [
                        'accumulated_in'  => $currentIn,
                        'accumulated_out' => $newOut,
                    ]
                ]);
                $change = - ($newOut - $currentOut);
            } else {
                DailyPeopleFlow::create([
                    'device_id' => $this->data->device_id,
                    'date'      => $date,
                    'context'   => [
                        'accumulated_in'  => 0,
                        'accumulated_out' => $newOut,
                    ]
                ]);
            }
        });

        return $change;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> updateOccupancy
     * <Function> Update daily occupancy record based on change in count.
     *
     * @param int $count
     * @return self
     */
    private function updateOccupancy(int $count): self
    {
        DB::transaction(function () use ($count) {
            $date = Carbon::parse($this->data->created_at)->setTimezone('Asia/Manila');

            $this->occupancy = DailyOccupancy::where('room_id', $this->data->device->room_id)
                ->whereDate('date', $date)
                ->lockForUpdate()
                ->first();

            [$current, $max] = [
                @$this->occupancy->context['current_count'],
                @$this->occupancy->context['max_count'],
            ];

            $context = [
                'current_count' => max($current + $count, 0),
                'max_count'     => max($current + $count, $max),
            ];

            if ($this->occupancy) {
                $this->occupancy->update(['context' => $context]);
            } else {
                $this->occupancy = DailyOccupancy::create([
                    'room_id' => $this->data->device->room_id,
                    'date'    => $date,
                    'context' => $context,
                ]);
            }
        });

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> broadcastEvent
     * <Function> Broadcast new update for occupancy data.
     *
     * @return void
     */
    private function broadcastEvent(): void
    {
        OccupancyUpdated::broadcast($this->occupancy);
    }
}
