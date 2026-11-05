<?php

namespace App\Drivers\Commands;

use App\Contracts\Lorawan\ControlsContract;
use App\Exceptions\Lorawan\CommandException;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Str;

class MilesightControls extends ControlsContract
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
        // build the encoder class namespace
        $manufacturer = Str::ucfirst($this->device->manufacturer->name);

        // Build the full namespace path for the encoder class
        $encoderClass = "App\\Encoders\\{$manufacturer}\\{$this->device->model}";

        // Throw error if model does not have an encoder class.
        throw_unless(
            class_exists($encoderClass),
            new CommandException([
                'connection' => 'milesight',
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
            "fport"         => $rawAttributes['port'],
            "confirmed"     => true,
            "data"          => hex_to_base64($rawAttributes['payload']),
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
        $topic = "/{$this->device->gateway->mac_address}/downlink/{$this->device->dev_eui}";

        try {
            MQTT::publish($topic,  json_encode($this->payload()));
            MQTT::disconnect();
        } catch (\Exception $ex) {
            //@@todo Change Kerlink to Milesight
            throw new CommandException([
                'connection' => 'milesight',
                'code' => $ex->getCode(),
                'device' => $this->device,
            ]);
        }

        return $this;
    }
}
