<?php

namespace App\Exceptions\SwitchBot;

use App\Models\Activity;
use App\Models\SwitchBotDevice;
use Exception;
use Illuminate\Http\Client\Response;

class CommandException extends Exception
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $messageCode    (1.0) The message code use for logging activity.
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
     *
     * @var String
     */
    public $messageCode;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $action
     * <Function> The action needed to solve the current error.
     *            Mostly retrieve from the message list.
     *
     * @var String
     */
    public $action;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> __construct
     * <Function> Custom constructor for the custom exception.
     *
     * @param  \Illuminate\Http\Client\Response $response
     * @param  \App\Models\SwitchBotDevice      $device
     */
    public function __construct(Response $response, SwitchBotDevice $device)
    {
        parent::__construct($this->prepareMessage($response, $device), $response->status());
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> prepareMessage
     * <Function> Prepare message based on the message list using the error code
     *            encountered while processing command.
     *
     * @param  \Illuminate\Http\Client\Response $response
     * @param  \App\Models\SwitchBotDevice      $device
     * @return String
     */
    private function prepareMessage(Response $response, SwitchBotDevice $device): String
    {
        // Retrieve status from response for reference on what message to display.
        $status = $response->status() === 200 ? $response->json(['statusCode']) : $response->status();

        // Retrieve default error message if error is not on the list yet.
        $this->messageCode  = in_array($status, array_keys(__("errors.switch_bot.command")))
            ? __("errors.switch_bot.command.{$status}") : 'E000';

        // Prepare action message.
        $this->action = get_action(Activity::TYPE_ERROR, $this->messageCode);

        // Return error message.
        return get_message(Activity::TYPE_ERROR, $this->messageCode, [
            'device' => optional($device)->name,
        ]);
    }
}
