<?php

namespace App\Exceptions\SwitchBot;

use App\Models\Activity;
use App\Models\SwitchBotDevice;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class FetchStatusException extends Exception
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $code           (1.0) The message code use for logging activity.
    // $action         (2.0) The action needed to solve the current error.
    //                       Mostly retrieve from the message list.
    // __construct     (3.0) Custom constructor for the custom exception.
    // prepareMessage  (4.0) Prepare message based on the message list using the
    //                       error code encountered while processing command.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $messageCode
     * <Function> The message code use for logging activity.
     *            With default value of 'E000' for uncaught errors.
     *
     * @var string
     */
    public $code = 'E000';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $action
     * <Function> The action needed to solve the current error.
     *            Mostly retrieve from the message list.
     *
     * @var string
     */
    public $action;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> __construct
     * <Function> Custom constructor for the custom exception.
     *
     * @param  \App\Models\SwitchBotDevice      $device
     */
    public function __construct(
        Response|RequestException $response,
        SwitchBotDevice $device
    ) {
        parent::__construct(
            $this->prepareMessage($response, $device)
        );
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> prepareMessage
     * <Function> Prepare message based on the message list using the error code
     *            encountered while processing command.
     *
     * @param  \App\Models\SwitchBotDevice      $device
     * @return string
     */
    private function prepareMessage(Response|RequestException $response, SwitchBotDevice $device): string
    {
        // Request exception
        if ($response instanceof RequestException) {
            $this->code = 'E022';
        }

        // Normal HTTP response
        if ($response instanceof Response) {

            if ($response->failed()) {
                $this->code = 'E022';
            }

            if ($response->json('statusCode') !== 100) {
                $this->code = 'E023';
            }
        }

        // Prepare action message.
        $this->action = get_action(Activity::TYPE_ERROR, $this->code);

        // Retrieve message base on code and return
        return get_message(Activity::TYPE_ERROR, $this->code, [
            'device' => optional($device)->name,
        ]);
    }
}
