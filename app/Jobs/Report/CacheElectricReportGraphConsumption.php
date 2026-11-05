<?php

namespace App\Jobs\Report;

use App\Models\Activity;
use App\Models\Admin;
use App\Notifications\AdminNotification;
use App\Services\Report\ElectricMonitoringReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheElectricReportGraphConsumption implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // CACHE_DURATION         (1.0) Cache duration for report data.
    // __construct            (2.0) Initialize job with validated input data,
    //                              job ID, and output filename.
    // handle                 (3.0) Cache trend report data.
    // failed                 (4.0) Handle job failure by dispatching failure
    //                              status and details.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> CACHE_DURATION
     * <Function> Cache duration for report data.
     *
     * @var int
     */
    public const CACHE_DURATION = 86400;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $user
     * <Function> The user instance to receive the notification.
     *
     * @var mixed
     */
    private $user;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Initialize job with validated input data, job ID, and output filename.
     *
     * @param array $validated
     * @param string $jobId
     * @param string $userId
     */
    public function __construct(
        private array $validated,
        private string $jobId,
        private string $userId,
        private string $filename,
    ) {
        $this->user = Admin::find($this->userId);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> handle
     * <Function> Cache trend report data.
     *
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return void
     */
    public function handle(ElectricMonitoringReportService $service): void
    {
        $expiration = self::CACHE_DURATION;
        $id = $this->jobId;
        $data = $this->validated;

        Cache::put("hourly_consumption_report_{$id}", $service->loadHourlyConsumptionData($data), $expiration);
        Cache::put("monthly_consumption_report_{$id}", $service->loadMonthlyConsumptionData($data), $expiration);

        CacheElectricReportGraphTrends::dispatch(
            $this->validated,
            $this->jobId,
            $this->userId,
            $this->filename
        )->onConnection(connection: 'gcloud');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> failed
     * <Function> Handle job failure by dispatching failure status and details.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Report generation failed', [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        $this->user?->notify(new AdminNotification([
            'nature' => Activity::TYPE_ERROR,
            'title' => 'Report Generation Failed',
            'message' => "Failed to generate report: {$this->filename}",
            'url' => null
        ]));
    }
}
