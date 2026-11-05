<?php

namespace App\Http\Requests\Api\Lorawan;

use App\Models\LorawanDevice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class EndpointRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // DEFAULT_HANDLER            (1.0) The default uplink handler for lorawan.
    // MILESIGHT_DECODED_HANDLER  (2.0) The uplink handler for milesight decoded
    //                                  lorawan uplink.
    // $handler                   (3.0) Get the uplink data handler.
    // authorize                  (4.0) Determine if request is allowed.
    // devEUI                     (5.0) Return the dev eui of device depending
    //                                  on request origin.
    // payload                    (6.0) Get the payload depending on the request
    //                                  origin.
    // rules                      (7.0) Shows the required rules.
    // initiator                  (8.0) Get the initiator from the request
    //                                  headers.
    // device                     (9.0) Retrieve the LoRaWAN device using devEUI.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> DEFAULT_HANDLER
     * <Function> The default uplink handler for lorawan.
     *
     * @var String
     */
    const DEFAULT_HANDLER = "default";

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> MILESIGHT_DECODED_HANDLER
     * <Function> The uplink handler for milesight decoded lorawan uplink.
     *
     * @var String
     */
    const MILESIGHT_DECODED_HANDLER = "milesight_decoded_handler";

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $handler
     * <Function> Get the uplink data handler.
     *
     * @var String
     */
    public $handler;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> authorize
     * <Function> Determine if request is allowed.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> devEUI
     * <Function> Return the dev eui of device depending on request origin.
     *
     * @return String
     */
    public function devEUI(): ?string
    {
        switch ($this->initiator()) {
            case 'Milesight':
                // Milesight Gateway user-agent header
                return $this->input('devEUI');
            case 'Thingpark':
                // TPE Gateway user-agent header
                return $this->input('DevEUI_uplink.DevEUI');
            case 'ChirpStack':
                // ChirpStack Gateway user-agent header
                return $this->input('deviceInfo.devEui');
            case 'ug63':
                return $this->json('devEUI');
            default:
                // Kerlink or other user-agent header -> can be extended
                return $this->input('end_device_id');
        }
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> payload
     * <Function> Get the payload depending on the request origin.
     *
     * @return String|array
     */
    public function payload()
    {
        switch ($this->initiator()) {
            case 'Milesight':
                // Milesight Gateway user-agent header
                $this->handler = $this->input('decoded') ? self::MILESIGHT_DECODED_HANDLER : self::DEFAULT_HANDLER;
                switch ($this->handler) {
                    case self::MILESIGHT_DECODED_HANDLER:
                        return $this->input('data');
                    default:
                        return bin2hex(base64_decode($this->input('data')));
                }
            case 'Thingpark':
                // TPE Gateway user-agent header
                return $this->input('DevEUI_uplink.payload_hex');

            case 'ChirpStack':
                // ChirpStack Gateway user-agent header
                return bin2hex(base64_decode($this->input('data')));

            case 'ug63':
                // UG63 user-agent header
                return bin2hex(base64_decode(string: $this->json('data')));

            default:
                // Kerlink or other user-agent header -> can be extended
                return bin2hex(base64_decode($this->input('payload')));
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> rules
     * <Function> Shows the required rules.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> initiator
     * <Function> Get the initiator from the request headers.
     *
     * @return string|null
     */
    private function initiator(): ?string
    {
        if ($this->header('user-agent') == 'cloudamqp-webhook')
            return 'ug63';

        return $this->header('X-Uplink-Initiator');
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> device
     * <Function> Retrieve the LoRaWAN device using devEUI.
     *
     * Queries the database using the resolved devEUI to find the corresponding
     * device. If the device does not exist, a log action may be triggered.
     *
     * @return \App\Models\LorawanDevice|null
     */
    public function device(): ?LorawanDevice
    {
        $device = LorawanDevice::where('dev_eui', $this->devEUI())->first();

        if (!blank($this->devEUI()) && blank($device)) {
            Log::error('Device not registered', [
                'dev_eui' => $this->devEUI()
            ]);
        }

        return $device;
    }
}
