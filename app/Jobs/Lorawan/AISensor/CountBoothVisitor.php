<?php

namespace App\Jobs\Lorawan\AISensor;

use App\Events\Lorawan\AISensor\BoothVisitorCountUpdated;
use App\Models\DeviceLatestData;
use App\Models\HourlyVisitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class CountBoothVisitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $visitorData          (1.0) The newly saved LoRaWAN device data.
    // __construct           (2.0) Create a new job instance.
    // handle                (3.0) Execute the job logic.
    // countVisitors         (4.0) Count hourly visitor
    // computeDailyAndTotal  (5.0) Compute daily and total visitor count
    // broadcastEvent        (6.0) Broadcast new update for booth visitor count.
    // failed                (7.0) Handle a job failure.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $visitorData
     * <Function> Container for visitor data for broadcasting
     *
     * @var array
     */
    protected array $visitorData;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance.
     *
     * @param  \App\Models\LorawanDeviceData $data
     */
    public function __construct(
        protected DeviceLatestData $data
    ) {
        $this->visitorData = [];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> handle
     * <Function> Execute the job logic.
     *
     * Process the payload by detecting type and updating occupancy accordingly.
     *
     * @return void
     */
    public function handle(): void
    {
        // hardcoded as of now as it will be use in event
        if ($this->data->device->dev_eui === '24E124600E033885') {
            $this->countVisitors();

            $this->computeDailyAndTotal();

            $this->broadcastEvent();
        }
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> countVisitors
     * <Function> Count hourly visitor
     *
     * @return void
     */
    protected function countVisitors()
    {
        $previousCount = $this->data->context['people_counter_old']['value'] ?? 0;
        $currentCount = $this->data->context['people_counter']['value'] ?? 0;

        // Compute the delta
        $delta = max(0, (int)$currentCount - (int)$previousCount);

        // Get or create the row for this hour, then accumulate the delta
        $row = HourlyVisitor::firstOrCreate(
            ['datetime' => now()->startOfHour()],
            ['value' => 0]
        );

        // Update value base on delta
        $row->increment('value', $delta);

        // Get hourly record
        $this->visitorData['hourly'] = [
            $row->datetime->copy()->setTimezone('Asia/Manila')->format('Y/m/d H:i'),
            $row->value
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> computeDailyAndTotal
     * <Function> Compute daily and total visitor count
     *
     * @return void
     */
    protected function computeDailyAndTotal()
    {
        $startOfDay = Carbon::now('Asia/Manila')->startOfDay()->utc();

        // Daily visitors (today)
        $dailyVisitors = HourlyVisitor::query()
            ->where('datetime', '>=', $startOfDay)
            ->sum('value');

        $this->visitorData['daily'] = (int) $dailyVisitors;

        // Total visitors (all time)
        $totalVisitors = HourlyVisitor::sum('value');

        $this->visitorData['total'] = (int) $totalVisitors;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> broadcastEvent
     * <Function> Broadcast new update for booth visitor count.
     *
     *
     * @return void
     */
    private function broadcastEvent(): void
    {
        BoothVisitorCountUpdated::broadcast($this->visitorData);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
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
}
