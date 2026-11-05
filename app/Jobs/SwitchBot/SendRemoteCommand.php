<?php

namespace App\Jobs\SwitchBot;

use App\Events\SwitchBot\NewSwitchBotRemoteData;
use App\Models\{DeviceLatestData, SwitchBotRemote, SwitchBotRemoteData};
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Cache, Http, Log};
use Throwable;

class SendRemoteCommand implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct    (1.0) Create a new job instance.
    // handle         (2.0) Execute the job.
    // failed         (3.0) Handle a job failure.
    // sendCommand    (4.0) Send an API request to command the SwitchBot remote.
    // saveDeviceData (5.0) Save the data if the command was successful.
    // broadcastData  (6.0) Broadcast the data to the client side.

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance.
     *
     * @param SwitchBotRemote $remote
     * @param array $body
     * @param mixed $data
     * @return void
     *
     */
    public function __construct(private SwitchBotRemote $remote, private array $body, private $data) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handle
     * <Function> Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Early exit if command was cancelled
        if (!Cache::pull("sbremotecontrol-{$this->remote->id}")) {
            return;
        }

        $this->sendCommand()
            ->updateLatestDeviceData();

        if ($this->remote->enable_data_storage) {
            $this->persistDataToDatabase();
        }

        $this->broadcastData();
    }

    /**
     * <Layer number> (3.0)
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
     * <Layer number> (4.0)
     *
     * <Processing name> sendCommand
     * <Function> Send an API request to command the SwitchBot remote.
     *
     * @return self
     *
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    private function sendCommand(): self
    {

        $account = $this->remote->room->switchBotAccount;

        $response = Http::switchBotHttp($account->access_token, $account->secret_key)
            ->post("devices/{$this->remote->serial_number}/commands", $this->body);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        if ($response->json('statusCode') !== 100) {
            throw new Exception('System Error!');
        }

        return $this;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> saveDeviceData
     * <Function> Save the data if the command was succesful.
     *
     * @return self
     */
    private function persistDataToDatabase(): void
    {
        // Keep DB transaction for data consistency and use create() to trigger observers
        $this->remote->data()->create([
            'via' => SwitchBotRemoteData::VIA_API,
            'context' => $this->data,
            'received_at' => now()
        ]);
    }

    private function updateLatestDeviceData(): void
    {
        DeviceLatestData::updateOrCreate(
            [
                'device_type' => SwitchBotRemote::class,
                'device_id' => $this->remote->id
            ],
            [
                'context' => $this->data,
                'updated_at' => now()
            ]
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> broadcastData
     * <Function> Broadcast the data to the client side.
     *
     * @return self
     */
    private function broadcastData(): void
    {
        $deviceData = [
            'context' => $this->data,
            'device_id' => $this->remote->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        NewSwitchBotRemoteData::dispatch($this->remote, $deviceData);
    }
}
