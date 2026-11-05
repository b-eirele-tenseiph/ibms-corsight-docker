<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Lorawan\EndpointRequest;
use App\Services\Lorawan\PayloadHandlerResolver;
use Illuminate\Http\Response;

class LorawanEndpointController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __invoke       (1.0) Display the user's room dashboard page.
    // __construct    (2.0) Handles instantiation of the PayloadHandlerResolver.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __invoke
     * <Function> Handle the incoming request.
     *
     * @param  \App\Http\Requests\Api\Lorawan\EndpointRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(EndpointRequest $request)
    {
        $device  = $request->device();

        if (blank($device)) {
            return response()->json(['message' => 'Device not registered.'], 200);
        }

        $payload = $request->payload();

        // Resolve the handler and let it dispatch the job
        $handler = $this->resolver->resolve($device);
        $handler->handle($device, $payload);

        return response()->noContent();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Handles instantiation of the PayloadHandlerResolver.
     *
     * @param  \App\Services\Lorawan\PayloadHandlerResolver;
     * */
    public function __construct(protected PayloadHandlerResolver $resolver) {}
}
