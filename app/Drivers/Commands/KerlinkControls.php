<?php

namespace App\Drivers\Commands;

use App\Contracts\Lorawan\ControlsContract;
use App\Exceptions\Lorawan\CommandException;
use Illuminate\Support\Str;
use PhpMqtt\Client\Facades\MQTT;

class KerlinkControls extends ControlsContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // encoder    (1.0) Return the encoder based on the manufacturer and model.
    // payload    (2.0) Generate payload for the device.
    // send       (3.0) Send command to execute for the lorawan device.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> encoder
     * <Function> Return the encoder based on the manufacturer and model.
     *
     * @return object
     */
    public function encoder(): object
    {
        $manufacturer = Str::ucfirst($this->device->manufacturer->name);

        // Build the full namespace path for the encoder class
        $encoderClass = "App\\Encoders\\{$manufacturer}\\{$this->device->model}";

        // Throw error if model does not have an encoder class.
        throw_unless(
            class_exists($encoderClass),
            new CommandException([
                'connection' => 'kerlink',
                'code' => 2001,
                'device' => $this->device,
            ])
        );

        return new $encoderClass($this->device);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> payload
     * <Function> Generate payload for the device.
     *
     * @return array
     */
    public function payload(): array
    {
        // Parse the raw attributes API readable for the command.
        $rawAttributes = $this->encoder()->encode($this->command);

        // Setup the values for the API readable attributes
        return [
            "end_device_id" => strtoupper($this->device->dev_eui),
            "port"          => $rawAttributes['port'],
            "acknowledge"   => true,
            "payload"       => hex_to_base64($rawAttributes['payload']),
            "try_number"    => (int) config('lorawan.tries')
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> send
     * <Function> Send command to execute for the lorawan device.
     *
     * @return self
     */
    public function send(): self
    {
        try {
            MQTT::publish($this->device->gateway->mac_address,  json_encode($this->payload()));
            MQTT::disconnect();
        } catch (\Exception $ex) {
            throw new CommandException([
                'connection' => 'kerlink',
                'code' => $ex->getCode(),
                'device' => $this->device,
            ]);
        }

        return $this;
    }
}
