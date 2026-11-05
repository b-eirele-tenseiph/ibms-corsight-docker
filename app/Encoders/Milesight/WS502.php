<?php

namespace App\Encoders\Milesight;

use App\Contracts\Lorawan\EncoderContract;
use App\Models\LorawanDeviceData;

class WS502 extends EncoderContract
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
        return ['turnOnS1', 'turnOnS2', 'turnOffS1', 'turnOffS2'];
    }
}
