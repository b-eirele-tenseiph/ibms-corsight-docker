<?php

namespace App\Providers;

use App\Services\SwitchBot\SwitchBotMacroService;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class SwitchBotServiceProvider extends ServiceProvider
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // register             (1.0) Register services required by the application.
    // boot                 (2.0) Bootstrap any application services.

    /**
     * <Layer number> (1.0)
     *
     * Processing name: boot
     * Function: Register any application services.
     *
     * @return void
     */
    public function register(): void {}

    /**
     * <Layer number> (2.0)
     *
     * Processing name: boot
     * Function: Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Define a macro on the HTTP client for SwitchBot API requests
        Http::macro('switchBotHttp', function (?string $access_token, ?string $secret_key) {
            return (new SwitchBotMacroService($access_token, $secret_key))->createMacro($this);
        });

        // SwitchBotDevice API headers to an existing PendingRequest instance.
        PendingRequest::macro('switchBotPending', function (?string $access_token, ?string $secret_key) {
            return (new SwitchBotMacroService($access_token, $secret_key))->createMacro($this);
        });
    }
}
