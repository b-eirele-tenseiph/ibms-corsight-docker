<?php

namespace App\Providers;

use App\Managers\LorawanManager;
use App\Models\LorawanGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class LorawanServiceProvider extends ServiceProvider
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
        // Define a macro on the HTTP client for Lorawan
        Http::macro('lorawan', function (string|LorawanGateway $target) {
            return LorawanManager::fromGateway($target)->macro();
        });
    }
}
