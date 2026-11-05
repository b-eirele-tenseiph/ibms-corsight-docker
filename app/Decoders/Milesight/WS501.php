<?php

namespace App\Decoders\Milesight;

use App\Contracts\Milesight\DecoderContract as MilesightDecoder;

class WS501 extends MilesightDecoder
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // dataTypes    (1.0) The data types that will be saved to the database.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> dataTypes
     * <Function> The data types that will be saved to the database.
     *            If returns an empty array, save all data types.
     *
     * @return string[]
     */
    protected function dataTypes(): array
    {
        return ['switch_1', 'power_consumption'];
    }
}
