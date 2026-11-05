<?php

namespace App\Contracts\Lorawan;

use App\Models\LorawanDevice;

abstract class ControlsContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $command      (1.0) The command to be executed.
    // $device       (2.0) The device the command will be executed.
    // command       (3.0) Set the command to be executed.
    // device        (4.0) Set the device to be executed.
    // encoder       (5.0) Set the encoder of the device.
    // payload       (6.0) Set the payload to be sent to the API.
    // send          (7.0) Send the payload to the api for command execution.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $command
     * <Function> The command to be executed.
     *
     * @var array
     */
    protected array $command;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $device
     * <Function> The device the command will be executed.
     *
     * @var \App\Models\LorawanDevice
     */
    protected LorawanDevice $device;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> command
     * <Function> Set the command to be executed.
     *
     * @param array $command
     *
     * @return self
     */
    public function command(array $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> device
     * <Function> Set the device to be executed.
     *
     * @param \App\Models\LorawanDevice $device
     *
     * @return self
     */
    public function device(LorawanDevice $device): self
    {
        $this->device = $device;

        return $this;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> encoder
     * <Function> Set the encoder of the device.
     *
     * @return object
     */
    abstract protected function encoder();

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> payload
     * <Function> Set the payload to be sent to the API.
     *
     * @return array
     */
    abstract protected function payload();

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> send
     * <Function> Send the payload to the API for command execution.
     *
     * @return \App\Contracts\Lorawan\ControlsContract
     */
    abstract protected function send();
}
