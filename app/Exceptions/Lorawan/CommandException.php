<?php

namespace App\Exceptions\Lorawan;

use App\Models\Activity;
use Exception;

class CommandException extends Exception
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct     (1.0) Custom constructor for the custom exception.
    // prepareMessage  (2.0) Prepare message based on the message list using the
    //                       error code.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Custom constructor for the custom exception.
     *
     * @param array $response
     */
    public function __construct(
        protected array $response
    ) {
        parent::__construct($this->prepareMessage($response));
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> prepareMessage
     * <Function> Prepare message based on the message list using the error code.
     *
     * @param array $response
     *
     * @return string
     */
    private function prepareMessage(array $response): string
    {
        ['connection' => $connection, 'code' => $code, 'device' => $device] = $response + ['connection' => null, 'device' => null];

        $messageCode = __("errors.lorawan.command.{$connection}")[$code] ?? 'E000';

        return get_message(Activity::TYPE_ERROR, $messageCode, [
            'device' => optional($device)->name,
        ]);
    }
}
