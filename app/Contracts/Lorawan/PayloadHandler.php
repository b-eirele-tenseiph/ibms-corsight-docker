<?php

namespace App\Contracts\Lorawan;

use App\Models\LorawanDevice;

interface PayloadHandler
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle     (1.0) Handle the incoming payload (e.g. dispatch a job).
    // canHandle  (2.0) Determine if the handler is applicable for a device.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle the incoming payload (e.g. dispatch a job).
     *
     * This method is responsible for processing or dispatching logic
     * when an uplink payload is received for a given LoRaWAN device.
     *
     * @param  \App\Models\LorawanDevice  $device
     * @param  string  $payload
     * @return void
     */
    public function handle(LorawanDevice $device, string $payload): void;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> canHandle
     * <Function> Determine if the handler is applicable for a device.
     *
     * Used to check whether the handler supports the specified device.
     *
     * @param  \App\Models\LorawanDevice  $device
     * @return bool
     */
    public function canHandle(LorawanDevice $device): bool;
}
