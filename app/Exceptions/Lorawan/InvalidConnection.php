<?php

namespace App\Exceptions\Lorawan;

use App\Contracts\Lorawan\ControlsContract;
use Exception;

class InvalidConnection extends Exception
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // classIsNotValid		(1.0) Error if commandable class isn't recognized.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> classIsNotValid
     * <Function> Error if commandable class isn't recognized.
     *
     * @param string $className
     *
     * @return static
     */
    public static function classIsNotValid(string $className): static
    {
        return new static("Unsupported connection class: $className. Ensure it extends or implements " . ControlsContract::class . ".");
    }
}
