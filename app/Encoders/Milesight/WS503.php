<?php

namespace App\Encoders\Milesight;

use App\Contracts\Lorawan\EncoderContract;

class WS503 extends EncoderContract
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
        return ['turnOnS1', 'turnOnS2', 'turnOnS3', 'turnOffS1', 'turnOffS2', 'turnOffS3'];
    }
}
