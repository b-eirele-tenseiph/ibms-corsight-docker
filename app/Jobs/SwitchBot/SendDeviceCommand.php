<?php

namespace App\Jobs\SwitchBot;

use App\Exceptions\SwitchBot\CommandException;
use App\Models\{Activity, SwitchBotDevice};
use App\Services\ActivityLogger\ActivityLoggerService;
use Illuminate\Bus\{Batchable, Queueable};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use App\Traits\ActivityLog\LogsCommand;
use Illuminate\Support\Facades\{Log, Http};
use Throwable;
use Exception;
use Illuminate\Support\Str;

class SendDeviceCommand implements ShouldQueue
{

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // MESSAGE_CODE   (1.0)  The message code use for logging activity.
    // $serialNumber  (2.0)  The serial number (MAC address) of the device.
    // $token         (3.0)  The API token associated with the device.
    // $secret        (4.0)  The secret key associated with the account.
    // $response      (5.0)  The response from API after sending request.
    // __construct    (6.0)  Create a new job instance.
    // handle         (7.0)  Execute the action.
    // failed         (8.0)  Handle a job failure.
    // sendCommand    (9.0)  Send an API request to command the SwitchBot device.
    // catchError     (10.0) Catch error if present and handle it using custom
    //                       exception.
    // saveCommandLog (11.0) Save activity log upon successful request.

    use Batchable,
        Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels,
        LogsCommand;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> MESSAGE_CODE
     * <Function> The message code use for logging activity.
     *
     * @var string
     */
    const MESSAGE_CODE = 'I000';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $serialNumber
     * <Function> The serial number (MAC address) of the device.
     *
     * @var string
     */
    private $serialNumber;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $token
     * <Function> The API token associated with the device.
     *
     * @var string
     */
    private string $token;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $secret
     * <Function> The secret key associated with the account.
     *
     * @var string
     */
    private string $secret;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $response
     * <Function> The response from api after sending request.
     *
     * @var \Illuminate\Http\Client\Response
     */
    private $response;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance.
     *
     * @param \App\Models\SwitchBotDevice $device
     * @param array $body
     * @return void
     */
    public function __construct(private SwitchBotDevice $device, private array $body)
    {
        $this->serialNumber = Str::of($device->serial_number)->upper();
        $this->token        = $device->room->switchBotAccount->access_token;
        $this->secret       = $device->room->switchBotAccount->secret_key;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> handle
     * <Function> Execute the action.
     *
     * @param  $device
     * @return void
     */
    public function handle(): void
    {
        $this->sendCommand()->catchError()->saveCommandLog();
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        // Error handling
        Log::error('Job Failed', [
            'task' => __CLASS__,
            'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> sendCommand
     * <Function> Send an API request to command the SwitchBot device.
     *
     * @return self
     *
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    private function sendCommand(): self
    {
        // SwitchBot version 1.1
        $this->response = Http::switchBotHttp($this->token, $this->secret)
            ->post("devices/{$this->serialNumber}/commands", $this->body);

        return $this;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> catchError
     * <Function> Catch error if present and handle it using custom exception.
     *
     * @return self
     */
    private function catchError(): self
    {
        throw_unless(
            $this->response->successful() && $this->response->json(['statusCode']) === 100,
            new CommandException($this->response, $this->device)
        );

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> saveCommandLog
     * <Function> Save activity log upon successful request.
     *
     * @return self
     */
    private function saveCommandLog(): self
    {
        ActivityLoggerService::info([
            'code'    => self::MESSAGE_CODE,
            'message' => get_message(Activity::TYPE_INFO, (string) self::MESSAGE_CODE, [
                'command' => $this->body['command'],
                'device' => $this->device->name,
            ]),
            'device' =>  $this->device
        ]);

        return $this;
    }
}
