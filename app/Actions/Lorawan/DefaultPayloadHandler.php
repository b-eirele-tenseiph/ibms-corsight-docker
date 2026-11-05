<?php

namespace App\Actions\Lorawan;

use App\Contracts\Lorawan\PayloadHandler;
use App\Jobs\Lorawan\HandleUplinkDataDefault;
use App\Models\LorawanDevice;

class DefaultPayloadHandler implements PayloadHandler
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle    (1.0) Handle the default uplink payload for a given device.
    // canHandle (2.0) Determine if this handler can process the device payload.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle the default uplink payload for a given device.
     *
     * Dispatches a job to process the payload using the default handler.
     *
     * @param  \App\Models\LorawanDevice  $device
     * @param  string  $payload
     * @return void
     */
    public function handle(LorawanDevice $device, string $payload): void
    {
        // Dispatch the default handler job
        HandleUplinkDataDefault::dispatch($device, $payload);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> canHandle
     * <Function> Determine if this handler can process the device payload.
     *
     * Always returns true for the default handler.
     *
     * @param  \App\Models\LorawanDevice  $device
     * @return bool
     */
    public function canHandle(LorawanDevice $device): bool
    {
        return true;
    }
}
