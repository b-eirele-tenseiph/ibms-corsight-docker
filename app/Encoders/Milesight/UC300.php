<?php

namespace App\Encoders\Milesight;

use App\Contracts\Lorawan\EncoderContract;
use App\Models\LorawanDevice;
use App\Structs\Modbus\ModbusRTUFrame;

class UC300 extends EncoderContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct       (1.0) Create a new event instance.
    // setPDU            (2.0) Set the PDU for the encoder.
    // methods           (3.0) List all commands that can be sent to the device.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new event instance.
     *
     * @param \App\Models\LorawanDevice $device
     *
     */
    public function __construct(
        LorawanDevice $device,
    ) {
        parent::__construct($device);
        $this->setPDU(new ModbusRTUFrame());
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> setPDU
     * <Function> Set the PDU for the encoder.
     *
     * @param \App\Structs\Modbus\ModbusRTUFrame $PDU
     *
     * @return void
     *
     */
    public function setPDU(ModbusRTUFrame $PDU): void
    {
        $this->PDU = $PDU;

        $context = (array) $this->device->context;
        $this->PDU->setSlaveID(dechex($context['slave_id']));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> methods
     * <Function> List all commands that can be sent to the device.
     *
     * @return array<string>
     *
     */
    protected function methods(): array
    {
        return ['powerOn', 'powerOff', 'changeTemperature', 'fanSpeedOff', 'fanSpeed1', 'fanSpeed2', 'fanSpeed3', 'fanSpeedAuto'];
    }
}
