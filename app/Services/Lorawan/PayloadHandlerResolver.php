<?php

namespace App\Services\Lorawan;

use App\Actions\Lorawan\DefaultPayloadHandler;
use App\Contracts\Lorawan\PayloadHandler;
use App\Models\LorawanDevice;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;

class PayloadHandlerResolver
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $handlers       (1.0) The registered payload handlers from the container.
    // __construct     (2.0) Initialize the resolver with tagged payload handlers.
    // resolve         (3.0) Resolves the appropriate payload handler for device.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $handlers
     * <Function> The registered payload handlers from the container.
     *
     * @var Collection<PayloadHandler>
     */
    protected Collection $handlers;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Initialize the resolver with tagged payload handlers.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        // Resolve all tagged 'payload_handlers' from the service container.
        // Laravel's tagged services are usually returned as a Generator, so we cast to Collection.
        $this->handlers = collect($container->tagged('payload_handlers'));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> resolve
     * <Function> Resolves the appropriate payload handler for device.
     *
     * Iterates through registered handlers and returns the first handler
     * that can process the given LoRaWAN device. Falls back to default handler.
     *
     * @param LorawanDevice $device
     * @return PayloadHandler
     */
    public function resolve(LorawanDevice $device): PayloadHandler
    {
        // Iterate through registered handlers and return the first one that can handle the payload.
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($device)) {
                return $handler;
            }
        }

        // If no specific handler matches, fall back to the DefaultPayloadHandler.
        return app(DefaultPayloadHandler::class);
    }
}
