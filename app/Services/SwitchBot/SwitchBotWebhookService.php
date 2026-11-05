<?php

namespace App\Services\SwitchBot;

use App\Exceptions\SwitchBot\SwitchBotWebhookSetupException;
use App\Models\Activity;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class SwitchBotWebhookService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct       (1.0)  Create new service instance.
    // withAccessToken   (2.0)  Returns an instance of the service initialized
    // AndSecretKey             with the token and secret key.
    // apiRequest        (3.0)  Sends an API request and returns the response.
    // queryUrl          (4.0)  Get all registered switch bot webhooks.
    // setup             (5.0)  Setup a new switch bot webhook.
    // queryDetails      (6.0)  Get the details of a switch bot webhook.
    // update            (7.0)  Update the switch bot webhook.
    // delete            (8.0)  Delete the switch bot webhook.
    // handleApiFailure  (9.0) Handle errors encountered on API request


    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create new service instance.
     *
     * @return void
     */
    public function __construct(
        public ?string $token,
        public ?string $secret,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> withAccessTokenAndSecretKey
     * <Function> Returns an instance of the service initialized with the token and secret key.
     *
     * @param  $token
     * @param  $secret
     * @return self
     */
    static function withAccessTokenAndSecretKey($token, $secret): SwitchBotWebhookService
    {
        return new self($token, $secret);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> apiRequest
     * <Function> Sends an API request and returns the response.
     *
     * @param string $token
     * @param string $secret
     * @return self
     */
    private function apiRequest(string $endpoint, array $params): mixed
    {
        $response = Http::switchBotHttp($this->token, $this->secret)->post($endpoint, $params);

        $response->failed() ? $this->handleApiFailure($response) : null;

        return $response;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> queryUrl
     * <Function> Get all registered switch bot webhooks.
     *
     */
    public function queryUrl(): string
    {
        $response = $this->apiRequest('/webhook/queryWebhook', ['action' => 'queryUrl']);
        $parsed = parse_url($response->json('body.urls')[0]);

        return "{$parsed['scheme']}://{$parsed['host']}";
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> setup
     * <Function> Setup a new switch bot webhook.
     *
     * @param string $url
     */
    public function setup(string $url): mixed
    {
        return $this->apiRequest('/webhook/setupWebhook', [
            'action' => 'setupWebhook',
            'url' => $url,
            'deviceList' => 'ALL',
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> queryDetails
     * <Function> Get the details of a switch bot webhook.
     *
     * @param string $url
     */
    public function queryDetails(string $url): mixed
    {
        return $this->apiRequest('/webhook/queryWebhook', [
            'action' => 'queryDetails',
            'urls' => [$url]
        ]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> update
     * <Function> Update the switch bot webhook.
     *
     * @param string $url
     * @param bool $enable
     */
    public function update(string $url, bool $enable = true): mixed
    {
        return $this->apiRequest('/webhook/updateWebhook', [
            'action' => 'updateWebhook',
            'config' => compact('url', 'enable'),
        ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> delete
     * <Function> Delete the switch bot webhook.
     *
     * @param string $url
     */
    public function delete(string $url): mixed
    {
        return $this->apiRequest('/webhook/deleteWebhook', [
            'action' => 'deleteWebhook',
            'url' => $url
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> handleApiFailure
     * <Function> Handle errors encountered on API request
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return void
     */
    protected function handleApiFailure(Response $response): void
    {
        try {
            throw_unless(
                $response->successful() && $response->json('statusCode') === 100,
                new SwitchBotWebhookSetupException($response)
            );
        } catch (\Throwable $e) {
            activity()
                ->inLog(Activity::TYPE_ERROR)
                ->withProperties([
                    'Code'   => $e->code,
                    'Action' => $e->action
                ])
                ->log($e->getMessage());
        }
    }
}
