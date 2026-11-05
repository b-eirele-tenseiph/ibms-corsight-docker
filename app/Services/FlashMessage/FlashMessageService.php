<?php

namespace App\Services\FlashMessage;

class FlashMessageService
{

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // success          (1.0) Generate flash message with type success.
    // info             (2.0) Generate flash message with type info.
    // warning          (3.0) Generate flash message with type warning.
    // error            (4.0) Generate flash message with type error.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> success
     * <Function> Generate flash message with type success.
     *
     * @param  string $message
     * @return array
     */
    public static function success(string $message): array
    {
        return [
            'message' => [
                'type' => 'success',
                'body'   => $message
            ],
        ];
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> info
     * <Function> Generate flash message with type info.
     *
     * @param  string $message
     * @return array
     */
    public static function info(string $message): array
    {
        return [
            'message' => [
                'type' => 'info',
                'body'   => $message
            ],
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> warning
     * <Function> Generate flash message with type warning.
     *
     * @param  string $message
     * @return array
     */
    public static function warning(string $message): array
    {
        return [
            'message' => [
                'type' => 'warning',
                'body'   => $message
            ],
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> error
     * <Function> Generate flash message with type error.
     *
     * @param  string $message
     * @return array
     */
    public static function error(string $message): array
    {
        return [
            'message' => [
                'type' => 'error',
                'body'   => $message
            ],
        ];
    }
}
