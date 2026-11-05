<?php

namespace App\Services\SwitchBot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Client\PendingRequest;

class SwitchBotMacroService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct          (1.0) Initializes the service with authentication
    //                            credentials.
    // macro                (2.0) Returns a configured HTTP PendingRequest instance for
    //                            the SwitchBot API based on the configured version.
    // macro                (3.0) Returns a configured HTTP PendingRequest instance for
    //                            the SwitchBotDevice API based on the configured version.
    // getDomainAndVersion  (4.0) Configure an existing PendingRequest for the SwitchBot API.
    // makeHeaders          (5.0) Builds the SwitchBot authentication headers.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initializes the service with authentication credentials.
     *
     * @param string $access_token
     * @param string $secret_key
     */
    public function __construct(
        public ?string $access_token,
        public ?string $secret_key,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> macro
     * <Function> Returns a configured HTTP PendingRequest instance for the
     *            SwitchBot API based on the configured version.
     *
     * @return /Illuminate\Http\Client\PendingRequest
     */
    public function createMacro($request = null): PendingRequest
    {
        [$domain, $version] = $this->getDomainAndVersion();
        $headers = $this->makeHeaders();

        return $request->asJson()
            ->baseUrl("{$domain}/{$version}")
            ->withHeaders($headers)
            ->acceptJson();
    }
    /**
     * <Layer number> (4.0)
     *
     * <Processing name>getDomainAndVersion
     * <Function> Configure an existing PendingRequest for the SwitchBot API.
     */
    private function getDomainAndVersion(): array
    {
        return [
            config('services.switchbot.domain'),
            config('services.switchbot.version'),
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> makeHeaders
     * <Function> Builds the SwitchBot authentication headers.
     */
    private function makeHeaders(): array
    {
        $timestamp = now()->getTimestampMs();
        $nonce = Str::uuid()->toString();
        $sign = Str::upper(base64_encode(
            hash_hmac(
                'sha256',
                $this->access_token . $timestamp . $nonce,
                $this->secret_key,
                true
            )
        ));

        return [
            'Authorization' => $this->access_token,
            'sign'          => $sign,
            'nonce'         => $nonce,
            't'             => $timestamp,
        ];
    }
}
