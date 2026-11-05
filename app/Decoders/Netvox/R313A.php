<?php

namespace App\Decoders\Netvox;

use App\Contracts\Netvox\DecoderContract as NetvoxDecoder;

class R313A extends NetvoxDecoder
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
        return ['battery', 'status'];
    }
}
