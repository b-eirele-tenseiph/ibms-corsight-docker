<?php

namespace App\Services\Corsight;

use Carbon\Carbon;

class CorsightDisplay
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // timestamp   (1.0)  Format a stored UTC timestamp for business
    //                     display (Asia/Manila).

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> timestamp
     * <Function> Format a stored UTC timestamp for business display.
     *
     * <Notes> The app stores UTC; business displays and reports use
     *         Asia/Manila (no DST). Single source for every Corsight UI
     *         timestamp — do not format timestamps elsewhere.
     *
     * @param  \Carbon\Carbon|null  $at
     * @return string|null
     */
    public static function timestamp(?Carbon $at): ?string
    {
        return $at?->timezone('Asia/Manila')->format('Y-m-d H:i:s');
    }
}
