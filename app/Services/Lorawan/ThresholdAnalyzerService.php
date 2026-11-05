<?php

namespace App\Services\Lorawan;

use App\Models\Activity;
use App\Models\LorawanDevice;
use App\Services\Emergency\EmergencyService;
use App\Traits\Notification\ShouldNotifyAdmin;
use Illuminate\Support\Collection;

class ThresholdAnalyzerService
{
    use ShouldNotifyAdmin;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // DEVICE_MODEL_PROPERTY   (1.0)  The models of the devices and their
    //                                respective threshold to-be-checked.
    // $device                 (2.0)  The lorawan device to be analyzed.
    // $dataCollection         (3.0)  The collection of data values to be
    //                                analyzed.
    // $log                    (4.0)  Array of name and description for creating
    //                                activity log.
    // $notificationData       (5.0)  Array of notification data for .
    //                                broadcasting.
    // $activityLog            (6.0)  The activity log instance.
    // analyze                 (7.0)  Main entry point to analyze device and
    //                                check for respected data threshold.
    // mapValue                (8.0)  Map the received data according to type
    //                                and current value.
    // temperature             (9.0)  Check temperature threshold if low or
    //                                high, continue if otherwise.
    // emergency               (10.0) Check emergency threshold, if status is
    //                                off, stop method, continue if otherwise
    // carbon_dioxide          (11.0) Check carbon dioxide level threshold if
    //                                low or high, continue otherwise
    // humidity                (12.0) Check humidity level threshold if low or
    //                                high, continue otherwise
    // fire                    (13.0) Check fire threshold if true or false
    // hightemp                (14.0) Check hightemp threshold if true or false
    // isOpen                  (15.0) Check window door sensor if true or false
    // saveActivityLog         (16.0) Save log for created log name and
    //                                description threshold.
    // notification            (17.0) The notification data as array to be
    //                                save and sent.
    // battery                 (18.0) Check battery voltage threshold if low or
    //                                critical, continue otherwise.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> DEVICE_MODEL_PROPERTY
     * <Function> The models of the devices and their respective threshold
     *            to-be-checked
     *
     * @var array<string,string[]>
     */
    const DEVICE_MODEL_PROPERTY = [
        'UC300'   => ['temperature'],
        'R312A'   => ['emergency', 'battery'],
        'RA0715'  => ['temperature', 'carbon_dioxide', 'humidity'],
        'RA02A'   => ['fire', 'hightemp', 'battery'],
        'R313A'   => ['isOpen', 'battery'],
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $device
     * <Function> The lorawan device to be analyzed.
     *
     * @var \App\Models\LorawanDevice
     */
    private $device;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $dataCollection
     * <Function> The collection of data values to be analyzed.
     *
     * @var \Illuminate\Support\Collection
     */
    private $dataCollection;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $log
     * <Function> Array of name and description for creating activity log.
     *
     * @var array
     */
    private $log;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $notificationData
     * <Function> Array of notification data for broadcasting.
     *
     * @var array
     */
    private $notificationData;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $activityLog
     * <Function> The activity log instance.
     *
     * @var \App\Models\Activity
     */
    private $activityLog;

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> analyze
     * <Function> Main entry point to analyze device and check for
     *            respected data threshold.
     *
     * @param  \App\Models\LorawanDevice $device
     * @param  \Illuminate\Support\Collection $data
     * @return void
     */
    public function analyze(LorawanDevice $device, Collection $data)
    {
        // Load room the device belongs to
        $this->device = $device->load('room');

        // Check if device should be checked according to models
        if (array_key_exists($this->device->model, self::DEVICE_MODEL_PROPERTY)) {
            // Map values to data collection according to type
            $this->mapValue($data);

            // Retrieve methods array depending on the model from MODELS constant
            if (key_exists($this->device->model, self::DEVICE_MODEL_PROPERTY)) {
                foreach (self::DEVICE_MODEL_PROPERTY[$this->device->model] as $method) {
                    // Iterate over methods for checking threshold
                    if ($this->$method()) {
                        // Save activiy log and send notification to admin
                        $this->saveActivityLog()->sendAdminNotification();
                    }
                }
            }
        }
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> mapValue
     * <Function> Map the received data according to type and current value
     *
     * @param  \Illuminate\Database\Eloquent\Collection $data
     * @return void
     */
    private function mapValue(Collection $data)
    {
        $this->dataCollection = $data->mapWithKeys(fn($item) => [
            $item['type'] => $item['context']['value']
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> temperature
     * <Function> Check temperature threshold if low or high, continue if otherwise
     *
     * @return boolean
     */
    private function temperature(): bool
    {
        // Get temperature value from collection
        $temperature = $this->dataCollection->get('temperature', null);

        // Instantiate indicator
        $indicator = '';

        // Check temperature values and update indicator accordingly
        switch (true) {
            case $temperature === null:
                return false;
            case $temperature > 35:
                $indicator = 'W001';
                break;
            case $temperature < 15:
                $indicator = 'W002';
                break;
            default:
                return false;
        }

        // Retrieve message from message list
        $message = get_message("warning", $indicator, ['room' => $this->device->room->name]);

        // Prepare log
        $this->log = [
            'code'        => $indicator,
            'name'        => Activity::TYPE_WARNING,
            'data' => __('log.temperature.message', ['temp' => $temperature]),
            'description' => $message
        ];

        // Create notification data for temperature warning
        $this->notificationData = [
            'nature' => Activity::TYPE_WARNING,
            'title' => __('defaults.temp_warning'),
            'message' => $message,
        ];

        return true;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> emergency
     * <Function> Check emergency threshold, if status is off, stop method, continue
     *            if otherwise
     *
     * @return boolean
     */
    private function emergency(): bool
    {
        // Check if emergency button has been triggered, return false if not
        if ($this->dataCollection->get('status')) {
            (new EmergencyService())->dispatch($this->device, 'emergencyMode');
        }

        // Return false to end emergency processing
        // It will already be dispatched by the emergency service
        return false;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> carbon_dioxide
     * <Function> Check carbon dioxide level threshold if low or high, continue
     *            otherwise
     *
     * @return boolean
     */
    private function carbon_dioxide(): bool
    {
        // Get temperature value from collection
        $carbonDioxideLevel = $this->dataCollection->get('carbon_dioxide', null);

        // Check temperature values and update indicator accordingly
        switch (true) {
            case $carbonDioxideLevel === null:
                return false;
            case $carbonDioxideLevel > 1000:
                $indicator = 'W003';
                break;
            case $carbonDioxideLevel < 400:
                $indicator = 'W004';
                break;
            default:
                return false;
        }

        // Retrieve message from message list
        $message = get_message("warning", $indicator, ['room' => $this->device->room->name]);

        // Prepare log
        $this->log = [
            'code'        => $indicator,
            'name'        => Activity::TYPE_WARNING,
            'data' => __('log.co2.message', ['co2' => $carbonDioxideLevel]),
            'description' => $message
        ];

        // Create notification data for temperature warning
        $this->notificationData = [
            'nature' => Activity::TYPE_WARNING,
            'title' => __('defaults.co2_critical'),
            'message' => $message,
        ];

        return true;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> humidity
     * <Function> Check humidity level threshold if low or high, continue
     *            otherwise
     *
     * @return boolean
     */
    private function humidity(): bool
    {
        // Get humidity value from collection
        $humidity = $this->dataCollection->get('humidity', null);

        // Instantiate indicator
        $indicator = '';

        // Check humidity values and update indicator accordingly
        switch (true) {
            case $humidity === null:
                return false;
            case $humidity > 60:
                $indicator = 'W005';
                break;
            case $humidity < 40:
                $indicator = 'W006';
                break;
            default:
                return false;
        }

        // Retrieve message from message list
        $message = get_message("warning", $indicator, ['room' => $this->device->room->name]);

        // Prepare log
        $this->log = [
            'code'        => $indicator,
            'name'        => Activity::TYPE_WARNING,
            'data'        => __('log.humidity.message', ['humidity' => $humidity]),
            'description' => $message
        ];

        // Create notification data for temperature warning
        $this->notificationData = [
            'nature' => Activity::TYPE_WARNING,
            'title' => __('defaults.humidity_level'),
            'message' => $message,
        ];

        return true;
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> fire
     * <Function> Check fire threshold if true or false
     *
     * @return boolean
     */
    private function fire(): bool
    {
        // If fire is detected, dispatch emergency service
        if ($this->dataCollection->get('fire')) {
            (new EmergencyService())->dispatch($this->device, 'fireMode');
        }

        // Return false to end emergency processing
        // It will already be dispatched by the emergency service
        return false;
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> hightemp
     * <Function> Check hightemp threshold if true or false
     *
     * @return boolean
     */
    private function hightemp(): bool
    {
        // If temperature is high, dispatch emergency service
        if ($this->dataCollection->get('hightemp')) {
            (new EmergencyService())->dispatch($this->device, 'fireMode');
        }

        // Return false to end emergency processing
        // It will already be dispatched by the emergency service
        return false;
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> isOpen
     * <Function> Check window door sensor if true or false
     *
     * @return boolean
     */
    private function isOpen(): bool
    {
        // If window/door is open, dispatch emergency service
        if ($this->dataCollection->get('status')) {
            (new EmergencyService())->dispatch($this->device, 'burglarMode');
        }

        // Return false to end emergency processing
        // It will already be dispatched by the emergency service
        return false;
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save log for created log name and description threshold
     *
     * @return self
     */
    public function saveActivityLog(): self
    {
        // Save log using activity log helper
        $this->activityLog = activity()
            ->inLog($this->log['name'])
            ->performedOn($this->device)
            ->causedBy($this->device)
            ->withProperties([
                'Code'   => $this->log['code'],
                'Floor'  => optional($this->device->room->floor)->name ?? '-not-set-',
                'Room'   => $this->device->room->name,
                'Data'   => $this->log['data']
            ])
            ->log($this->log['description']);

        return $this;
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> notification
     * <Function> The notification data as array to be save and sent.
     *
     * @return array
     */
    public function notification(): array
    {
        // Add url of logs for viewing
        $this->notificationData['url'] = route('admin.management.logs.index', [
            'search'    => $this->activityLog->id,
        ]);

        return $this->notificationData;
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> battery
     * <Function> Check battery voltage threshold if low or critical, continue otherwise
     *
     * @return boolean
     */
    private function battery(): bool
    {
        $battery = $this->dataCollection->get('battery', null);
        $indicator = '';

        if ($battery === null) {
            return false;
        }

        $maxVoltage = 3.0;
        $batteryPercent = ($battery / $maxVoltage) * 100;
        $batteryPercent = max(0, min(100, round($batteryPercent, 2)));

        switch (true) {
            case $batteryPercent <= 20:
                $indicator = 'W008';
                break;
            case $batteryPercent <= 50:
                $indicator = 'W007';
                break;
            default:
                return false;
        }

        $message = get_message('warning', $indicator, [
            'room'    => $this->device->room->name,
            'device'  => $this->device->name,
            'battery' => "{$batteryPercent}",
        ]);

        $this->log = [
            'code' => $indicator,
            'name' => Activity::TYPE_WARNING,
            'data' => __('log.battery.message', ['battery' => "{$batteryPercent}%"]),
            'description' => $message,
        ];

        $this->notificationData = [
            'nature'  => Activity::TYPE_WARNING,
            'title'   =>  __('defaults.battery_warning'),
            'message' => $message,
        ];

        return true;
    }
}
