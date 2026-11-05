<?php

namespace App\Exceptions\SwitchBot;

use App\Models\Activity;
use Exception;
use Illuminate\Http\Client\Response;

class SwitchBotWebhookSetupException extends Exception
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $code           (1.0) The default message code use for logging activity.
    // $action         (2.0) The action needed to solve the current error.
    // __construct     (3.0) Custom constructor for the custom exception.
    // prepareMessage  (4.0) Checks the API response for failure conditions
    //                       and updates the error code accordingly.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $messageCode
     * <Function> The default message code use for logging activity.
     *
     * @var string
     */
    public $code = 'E000';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $action
     * <Function> The action needed to solve the current error.
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
     * @param \Illuminate\Http\Client\Response $response
     */
    public function __construct(Response $response)
    {
        parent::__construct($this->prepareMessage($response));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> prepareMessage
     * <Function> Checks the API response for failure conditions and updates
     *            the error code accordingly.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return string
     */
    private function prepareMessage(Response $response): string
    {
        $this->code = $response->failed() ? 'E001' : ($response->json('statusCode') !== 100 ? 'E002' : $this->code);

        // Prepare the action message for the error code
        $this->action = get_action(Activity::TYPE_ERROR, $this->code);

        // Retrieve and return the message based on the error code
        return get_message(Activity::TYPE_ERROR, $this->code);
    }
}
