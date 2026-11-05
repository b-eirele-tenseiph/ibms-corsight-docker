<?php

namespace App\Jobs\SwitchBot;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Activity;
use App\Models\SwitchBotDevice;
use App\Traits\Notification\ShouldNotifyAdmin;
use Illuminate\Database\Eloquent\Builder;

class MonitorDeviceBatteryStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShouldNotifyAdmin;

    /**************************************************************************/
    /* Processing Heirarchy                                                   */
    /**************************************************************************/
    // $devices                (1.0) The SwitchBot devices to be analyzed.
    // $message                (2.0) The message based on the given message id.
    // $activityLog            (3.0) The activity log instance.
    // handle                  (4.0) Execute the job.
    // fetchLowBatteryDevices  (5.0) Get all latest data of SwitchBot devices
    //                               that has battery less than 60.
    // checkExistingLog        (6.0) Check if the log exists for the last 7 days
    // saveActivityLog         (7.0) Save activity log to database
    // notification            (8.0) Create notification array data.
    //                               Implemented from trait.
    // prepareMessage          (9.0) Retrieve message from message list using
    //                                id depending on which type of access
    //                                was recorded.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $devices
     * <Function> The SwitchBot devices to be analyzed.
     *
     * @var \App\Models\SwitchBotDevice
     */
    private $devices;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $message
     * <Function> The message based on the given message id.
     *
     * @var string
     */
    private $message;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $activityLog
     * <Function> The activity log instance.
     *
     * @var \App\Models\Activity
     */
    private Activity $activityLog;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> handle
     * <Function> Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->fetchLowBatteryDevices();

        $this->devices->each(function ($device) {

            $this->prepareMessage($device);

            $battery = $device->latestData->context['battery'];

            if (($battery > 9 && $battery < 60) && $this->checkExistingLog($device)) {
                return true;
            }

            $this
                ->saveActivityLog($device)
                ->sendAdminNotification();
        });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> fetchLowBatteryDevices
     * <Function> Get all latest data of SwitchBot devices that has battery less than 60.
     *
     * @return $devices
     */
    private function fetchLowBatteryDevices()
    {
        $this->devices = SwitchBotDevice::whereHas('latestData', function (Builder $query) {
            $query->whereRaw("JSON_EXTRACT(context, '$.battery') < 60");
        })->get();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> checkExistingLog
     * <Function> Check if the log exists for the last 7 days
     *
     * @return $devices
     */
    public function checkExistingLog($device): bool
    {
        return Activity::where('log_name', Activity::TYPE_WARNING)
            ->where('causer_id', $device->id)
            ->where('properties->battery', '>', 9)
            ->where('properties->battery', '<', 60)
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->exists();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save activity log to database
     *
     * @param mixed $device
     * @return self
     */
    private function saveActivityLog($device): self
    {
        $this->activityLog = activity()
            ->causedBy($device)
            ->inLog(Activity::TYPE_WARNING)
            ->withProperties([
                'Code' => 'W003',
                'Floor'  => optional($device->room->floor)->name ?? '-not-set-',
                'Room'   => $device->room->name,
                'Battery' => $device->latestData['context']['battery'] . '%'
            ])
            ->log($this->message);

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> notification
     * <Function> Create notification array data. Implemented from trait.
     *
     * @return array
     */
    public function notification(): array
    {
        return [
            'nature'  => Activity::TYPE_WARNING,
            'title'   => 'Low Battery',
            'message' => $this->message,
            'url' => route('admin.management.logs.index', [
                'search'    => $this->activityLog->id,
                'end_date'  => now()->toDateString(),
                'expanded'  => true
            ])
        ];
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> prepareMessage
     * <Function> Retrieve message from message list using id depending
     *            on which type of access was recorded.
     *
     * @param mixed $device
     * @return self
     */
    private function prepareMessage($device): self
    {
        $this->message = get_message(Activity::TYPE_WARNING, 'W007', [
            'device' => $device->name,
            'battery' => $device->latestData->context['battery'] ?? null,
            'room' => optional($device->room)->name
        ]);

        return $this;
    }
}
