<?php

namespace App\Http\Requests\Api\SwitchBot;

use App\Models\SwitchBotDevice;
use Illuminate\Foundation\Http\FormRequest;

class EndpointRequest extends FormRequest
{
    /******************************************************************************************/
    /* Processing Hierarchy                                                                   */
    /******************************************************************************************/
    // authorize                  (1.0) Determine if the user is authorized to make this request.
    // rules                      (2.0) Get the validation rules that apply to the request.
    // device                     (3.0) Try to fetch device in storage.
    // payload                    (4.0) Get the raw context payload from the request.
    // isDeviceKeypadTouch        (5.0) Check if device is of type Keypad Touch.
    // isKeypadCommandSuccessful  (6.0) Check if keypad command result is
    //                                   successful.



    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'eventType'             => 'required|string',
            'eventVersion'          => 'required|string',
            'context'               => 'required|array',
            'context.deviceType'    => 'sometimes|string',
            'context.deviceMac'     => 'required|string|exists:switch_bot_devices,serial_number',
            'context.timeOfSample'  => 'required|integer',
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> identifyDevice
     * <Function> Try to fetch device in storage.
     *
     * @return \App\Models\SwitchBotDevice|null
     */
    public function device(): ?SwitchBotDevice
    {
        $serialNumber = $this->input('context.deviceMac');

        $device = SwitchBotDevice::firstWhere('serial_number', $serialNumber);

        if (blank($device)) {
            // @@todo LOG ('Unable to process uplink data. Device not yet registered.');
        }

        return $device;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> payload
     * <Function> Get the raw 'context' payload array from the request.
     *
     * @return array|null
     */
    public function payload()
    {
        return $this->context;
    }


    /**
     * <Layer number> (5.0)
     *
     * <Processing name> isDeviceKeypadTouch
     * <Function> Check if device is of type Keypad Touch.
     *
     * @param  \App\Models\SwitchBotDevice|null $device
     * @return boolean
     */
    public function isDeviceKeypadTouch(?SwitchBotDevice $device): bool
    {
        return !!$device && $device->type->name == 'Keypad Touch';
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> isKeypadCommandSuccessful
     * <Function> Check if keypad command result is successful.
     *
     * @return boolean
     */
    public function isKeypadCommandSuccessful(): bool
    {
        return $this->input('context.result') == 'success';
    }
}
