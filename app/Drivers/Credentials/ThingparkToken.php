<?php

namespace App\Drivers\Credentials;

use App\Contracts\Lorawan\MacroContract;
use App\Exceptions\Lorawan\CommandException;
use App\Models\ThirdPartyAccessToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ThingparkToken extends MacroContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // generateToken        (1.0) Generate token base on config.
    // handleError          (2.0) Error handling when generating token fails.
    // saveToken            (3.0) Save newly generated token to storage.
    // baseUrl              (4.0) Get the baseUrl string for macro.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> generateToken
     * <Function> Generate token base on config.
     *
     * @return void
     */
    public function generateToken(): void
    {
        $oauth = $this->config['oauth'];

        $this->response = Http::asForm()->baseUrl($oauth['url'])
            ->post('/oauth/token', [
                'client_id'     => $oauth['client']['id'],
                'client_secret' => $oauth['client']['secret'],
                'grant_type'    => $oauth['grant_type'],
            ]);

        $this->data = $this->response;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handleError
     * <Function> Error handling when generating token fails.
     *
     * @return void
     */
    public function handleError(): void
    {
        throw_unless(
            $this->response->successful(),
            new CommandException([
                'connection'    => 'thingpark_token',
                'code'          => $this->response->status()
            ])
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> saveToken
     * <Function> Save newly generated token to storage.
     *
     * @return void
     */
    public function saveToken(): void
    {
        $generatedAt = Carbon::now();

        $tokenRecord = ThirdPartyAccessToken::updateOrCreate(
            [
                'name'          => $this->config['token_name']
            ],
            [
                'token_type'    => $this->data['token_type'],
                'scope'         => $this->data['scope'],
                'token'         => $this->data['access_token'],
                'generated_at'  => $generatedAt,
                'expires_at'    => $generatedAt->copy()->addSeconds($this->data['expires_in']),
            ]
        );

        $this->token = $tokenRecord->token;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> baseUrl
     * <Function> Get the baseUrl string for macro.
     *
     * @return string
     */
    public function baseUrl(): string
    {
        return $this->config['hostname'] . '/core/latest/api';
    }
}
