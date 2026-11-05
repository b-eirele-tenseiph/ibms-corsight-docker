<?php

namespace App\Actions\Lorawan;

use App\Contracts\Lorawan\PayloadHandler;
use App\Jobs\Lorawan\HandleUplinkDataADW300;
use App\Models\LorawanDevice;

class ADW300PayloadHandler implements PayloadHandler
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle    (1.0) Handle the ADW300 uplink payload for a given device.
    // canHandle (2.0) Determine if this handler can process the device payload.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle the ADW300 uplink payload for a given device.
     *
     * Dispatches a job to process the payload using the ADW300-specific handler.
     *
     * @param  \App\Models\LorawanDevice  $device
     * @param  string  $payload
     * @return void
     */
    public function handle(LorawanDevice $device, string $payload): void
    {
        // Dispatch the specific job for ADW300 device payload
        HandleUplinkDataADW300::dispatch($device, $payload);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> canHandle
     * <Function> Determine if this handler can process the device payload.
     *
     * Checks if the device is of model ADW300 and returns true if so.
     *
     * @param  \App\Models\LorawanDevice  $device
     * @return bool
     */
    public function canHandle(LorawanDevice $device): bool
    {
        $isADW300 = $device->model === 'ADW300';

        $isUC300WithValidSlave = $device->model === 'UC300'
            && in_array($device->context['slave_model'] ?? null, ['EPM5500P', 'ADW300']);

        return $isADW300 || $isUC300WithValidSlave;
    }
}
