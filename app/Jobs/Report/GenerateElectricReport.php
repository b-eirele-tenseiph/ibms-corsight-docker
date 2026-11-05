<?php

namespace App\Jobs\Report;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\{Admin, Activity};
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\{Http, Storage, URL};
use App\Models\File as ReportFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Number;

class GenerateElectricReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // MESSAGE_CODE         (1.0)  Activity log message code for report
    //                             generation.
    // $user                (2.0)  The user instance to receive the notification.
    // CLOUD_FUNCTION_URL   (3.0)  Cloud function URL for PDF generation.
    // $tries               (4.0)  Number of times the job may be attempted.
    // $timeout             (5.0)  Maximum HTTP request timeout in seconds.
    // $downloadUrl         (6.0)  The download url of the file.
    // $filename            (7.0)  The filename of the generated report.
    // __construct          (8.0)  Initialize job with validated input data,
    //                             job ID, and user id.
    // handle               (9.0)  Generate PDF report by caching data and
    //                             converting HTML to PDF via cloud function.
    // notifyUser           (10.0) Send notification to user with download link
    //                             and log activity.
    // saveActivityLog      (11.0) Save activity log for report generation.
    // saveFileToDatabase   (12.0) Save the file data into storage.
    // failed               (13.0) Handle job failure by dispatching failure
    //                             status and details.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> MESSAGE_CODE
     * <Function> Activity log message code for report generation.
     *
     * @var string
     */
    private const MESSAGE_CODE = 'A001';

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
     * <Layer number> (3.0)
     *
     * <Processing name> CLOUD_FUNCTION_URL
     * <Function> Cloud function URL for PDF generation.
     *
     * @var string
     */
    private const CLOUD_FUNCTION_URL = 'https://ibms-pdf-generator-979065976040.asia-northeast1.run.app/generate-report';

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $tries
     * <Function> Number of times the job may be attempted.
     *
     * @var int
     */
    private int $tries = 2;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $timeout
     * <Function> Maximum HTTP request timeout in seconds.
     *
     * @var int
     */
    private int $timeout = 600;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $downloadUrl
     * <Function> The download url of the file.
     *
     * @var int
     */
    private string $downloadUrl;


    /**
     * <Layer number> (8.0)
     *
     * <Processing name> __construct
     * <Function> Initialize job with validated input data, job ID, and user id.
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
     * <Layer number> (9.0)
     *
     * <Processing name> handle
     * <Function> Generate PDF report by caching data and converting HTML to PDF via cloud function.
     *
     * @return void
     */
    public function handle(): void
    {
        $path = "reports/{$this->filename}";

        $url = URL::temporarySignedRoute(
            'admin-electric-report-print',
            now()->addDay(),
            array_merge(['jobId' => $this->jobId], $this->validated)
        );

        $response = retry($this->tries, function () use ($url) {
            return Http::timeout($this->timeout)->post(self::CLOUD_FUNCTION_URL, ['url' => $url]);
        }, 5000);

        Storage::disk('gcs')->put(
            $path,
            $response->body(),
            ['ContentType' => 'application/pdf']
        );

        $this->downloadUrl = route('admin-electric-report-download', [
            'filename' => $this->filename
        ]);

        $this->saveFileToDatabase($path);
        $this->notifyUser();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> notifyUser
     * <Function> Send notification to user with download link and log activity.
     *
     * @param string $downloadUrl
     * @return void
     */
    private function notifyUser(): void
    {
        if (!$this->user) return;

        // Generate activity log message
        $log = $this->saveActivityLog();

        // Send notification to user
        $this->user->notify(new AdminNotification([
            'nature' => Activity::TYPE_INFO,
            'title' => trans('notification.electric_meter.report_generation_success.title'),
            'message' => trans('notification.electric_meter.report_generation_success.message'),
            'url' => env('MAIN_APP_URL', config('app.url')) . '/admin/management/logs?' . http_build_query([
                'search' => $log->id,
                'severity' => 'information'
            ]),
            'metadata' => [
                'filename' => $this->filename,
                'download_url' => $this->downloadUrl,
            ]
        ]));
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save activity log for report generation.
     *
     * @return \App\Models\Activity|null
     */
    private function saveActivityLog(): Activity|null
    {
        // Generate activity log message
        $message = get_message(Activity::TYPE_AUDIT, self::MESSAGE_CODE, [
            'user' => $this->user->name,
            'filename' => $this->filename
        ]);

        // Log the report generation activity
        return activity()
            ->performedOn($this->user)
            ->causedBy($this->user)
            ->inLog(Activity::TYPE_INFO)
            ->withProperties([
                'Code'    => 'A001',
                'Link' => $this->downloadUrl,
            ])
            ->log($message);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> saveFileToDatabase
     * <Function> Save the file data into storage.
     *
     * @param string $path
     * @return void
     */
    private function saveFileToDatabase(string $path): void
    {
        $disk = Storage::disk('gcs');

        ReportFile::create([
            'name' => $this->filename,
            'extension' => File::extension($this->filename),
            'size' => Number::fileSize($disk->size($path)),
            'path' => $this->downloadUrl,
            'admin_id' => $this->user->id
        ]);
    }

    /**
     * <Layer number> (13.0)
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
            'filename' => $this->filename,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        $this->user?->notify(new AdminNotification([
            'nature' => Activity::TYPE_ERROR,
            'title' => trans('notification.electric_meter.report_generation_fail.title'),
            'message' => trans('notification.electric_meter.report_generation_fail.message', ['filename' => $this->filename]),
            'url' => null
        ]));
    }
}
