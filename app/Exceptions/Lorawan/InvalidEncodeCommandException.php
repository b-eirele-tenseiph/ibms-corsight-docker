<?php

namespace App\Exceptions\Lorawan;

use App\Contracts\Lorawan\EncoderContract;
use Exception;

class InvalidEncodeCommandException extends Exception
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // commandIsNotValid	(1.0) Error if command isn't recognized.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> commandIsNotValid
     * <Function> Error if command isn't recognized.
     *
     * @param string $method
     *
     * @return static
     */
    public static function commandIsNotValid(string $method): static
    {
        return new static("Encode failed: command method '{$method}' does not exist in " . EncoderContract::class . ".");
    }
}
