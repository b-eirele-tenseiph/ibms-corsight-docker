<?php

namespace App\Jobs\Lorawan\ElectricMeter;

use App\Models\Activity;
use App\Models\DeviceLatestData;
use App\Models\LorawanDevice;
use App\Traits\Notification\ShouldNotifyAdmin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class SendCriticalLoadUnbalanceNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotifyAdmin;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $message                 (1.0) Holds the notification message content.
    // $activityLog             (2.0) The activity log instance.
    // __construct              (3.0) Create a new job instance.
    // handle                   (4.0) Execute the job.
    // handleUnbalanceThreshold (5.0) Checks current and voltage unbalance
    //                                values and triggers logging and admin
    //                                notification when critical thresholds are
    //                                exceeded.
    // notifyAdmin              (6.0) Notify all admin about load imbalance.
    // saveActivityLog          (7.0) Creates an error activity log entry for a
    //                                critical load unbalance event.
    // notification             (8.0) Builds the admin notification payload for
    //                                a critical load unbalance event.
    // prepareMessage           (9.0) Prepares the localized error message for a
    //                                critical load unbalance event.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $message
     * <Function> Holds the notification message content.
     *
     * @var string|null
     */
    private ?string $message = null;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $activityLog
     * <Function> The activity log instance.
     *
     * @var \App\Models\Activity|null
     */
    private ?Activity $activityLog = null;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance.
     *
     * @param \App\Models\DeviceLatestData $data
     */
    public function __construct(private DeviceLatestData $data) {}

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> handle
     * <Function> Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->handleUnbalanceThreshold($this->data);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> handleUnbalanceThreshold
     * <Function> Checks current and voltage unbalance values and triggers logging
     *            and admin notification when critical thresholds are exceeded.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    public function handleUnbalanceThreshold(DeviceLatestData $data): void
    {
        $currentUnbalance = $data->context['current_unbalance']['value'] ?? 0;
        $voltageUnbalance = $data->context['voltage_unbalance']['value'] ?? 0;

        if ($currentUnbalance >= 10) {
            $this->prepareMessage($data, 'current', $currentUnbalance);
            $this->notifyAdmin($data->device->id);
        }

        if ($voltageUnbalance >= 5) {
            $this->prepareMessage($data, 'voltage', $voltageUnbalance);
            $this->notifyAdmin($data->device->id);
        }
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> notifyAdmin
     * <Function> Notify all admin about load imbalance.
     *
     * @param string $deviceId
     * @return void
     */
    private function notifyAdmin(string $deviceId): void
    {
        $cacheKey = "admin_notify_unbalance_{$deviceId}";

        if (Cache::add($cacheKey, true, now()->addHours(8))) {
            $this->saveActivityLog($this->data->device)
                ->sendAdminNotification();
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Creates an error activity log entry for a critical load unbalance event.
     *
     * @param \App\Models\LorawanDevice $device
     * @return self
     */
    private function saveActivityLog(LorawanDevice $device): self
    {
        $this->activityLog = activity()
            ->causedBy($device)
            ->inLog(Activity::TYPE_ERROR)
            ->withProperties([
                'Code' => 'E024',
                'Floor' => optional($device->room->floor)->name ?? '-not-set-',
                'Room' => $device->room->name,
            ])
            ->log($this->message);

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> notification
     * <Function> Builds the admin notification payload for a critical load unbalance event.
     *
     * @return array
     */
    public function notification(): array
    {
        return [
            'nature' => Activity::TYPE_ERROR,
            'title' => __('notification.electric_meter.critical_load_unbalance.title'),
            'message' => $this->message,
            'url' => route('admin.management.logs.index', [
                'search' => $this->activityLog->id,
                'end_date' => now()->toDateString(),
                'expanded' => true
            ])
        ];
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> prepareMessage
     * <Function> Prepares the localized error message for a critical load unbalance event.
     *
     * @param \App\Models\DeviceLatestData $data
     * @param string $type
     * @param float $percentage
     * @return self
     */
    private function prepareMessage(DeviceLatestData $data, string $type, float $percentage): self
    {
        $this->message = get_message(Activity::TYPE_ERROR, 'E024', [
            'type' => $type,
            'device' => $data->device->name,
            'percentage' => $percentage
        ]);

        return $this;
    }
}
