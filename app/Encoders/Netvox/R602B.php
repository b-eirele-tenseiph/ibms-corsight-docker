<?php

namespace App\Encoders\Netvox;

use App\Contracts\Lorawan\EncoderContract;

class R602B extends EncoderContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // methods           (1.0) List all commands that can be sent to the device.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> methods
     * <Function> List all commands that can be sent to the device.
     *
     * @return array<string>
     *
     */
    protected function methods(): array
    {
        return ['fireMode', 'emergencyMode', 'burglarMode', 'doorbellMode', 'muteMode'];
    }
}
