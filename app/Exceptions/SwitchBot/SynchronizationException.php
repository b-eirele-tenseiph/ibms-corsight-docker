<?php

namespace App\Exceptions\SwitchBot;

use App\Models\Activity;
use Exception;
use Illuminate\Http\Client\Response;

class SynchronizationException extends Exception
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $code           (1.0) The message code use for logging activity.
    //                       With default value of 'E000' for uncaught errors.
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
     * @param  \Illuminate\Http\Client\Response $response
     * @param  string|null                      $account
     */
    public function __construct(Response $response, public ?string $account = null)
    {
        parent::__construct($this->prepareMessage($response));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> prepareMessage
     * <Function> Prepare message based on the message list using the error code
     *            encountered while processing command.
     *
     * @param  \Illuminate\Http\Client\Response $response
     * @return string
     */
    private function prepareMessage(Response $response)
    {
        // Change code value if API request failed
        if ($response->failed()) $this->code = 'E025';

        // Change code value if API response is invalid/failure
        if ($response->json('statusCode') !== 100) $this->code  = 'E026';

        // Prepare action message.
        $this->action = get_action(Activity::TYPE_ERROR, $this->code);

        // Retrieve message base on code and return
        return get_message(Activity::TYPE_ERROR, $this->code, [
            'account' => $this->account ?? trans('defaults.unknown'),
        ]);
    }
}
