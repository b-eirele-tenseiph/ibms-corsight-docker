<?php

namespace App\Providers\Lorawan;

use App\Actions\Lorawan\ADW300PayloadHandler;
use App\Actions\Lorawan\DefaultPayloadHandler;
use App\Services\Lorawan\PayloadHandlerResolver;
use Illuminate\Support\ServiceProvider;

class PayloadHandlerServiceProvider extends ServiceProvider
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // register (1.0) Register the payload handlers and resolver singleton.
    // boot     (2.0) Bootstrap any application services.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> register
     * <Function> Register the payload handlers and resolver singleton.
     *
     * Tags specific payload handlers for resolution, and binds
     * a singleton instance of the `PayloadHandlerResolver`.
     *
     * @return void
     */
    public function register()
    {
        // Tag all your payload handlers so they can be easily retrieved as a collection.
        // The order here matters for the resolver's `first()` or `foreach` loop.
        // More specific handlers should generally be tagged before more general ones (like the default).
        $this->app->tag([
            ADW300PayloadHandler::class,
            DefaultPayloadHandler::class, // Default should be last in consideration if its canHandle() is always true.
        ], 'payload_handlers');

        // Register the PayloadHandlerResolver as a singleton.
        // This means only one instance of the resolver will be created and reused throughout the app.
        $this->app->singleton(PayloadHandlerResolver::class, function ($app) {
            return new PayloadHandlerResolver($app);
        });
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> boot
     * <Function> Bootstrap any application services.
     *
     * Used for tasks that should occur after all services are registered.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
