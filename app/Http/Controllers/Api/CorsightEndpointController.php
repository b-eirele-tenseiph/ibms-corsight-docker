<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Corsight\EndpointRequest;
use App\Jobs\Corsight\HandleCorsightEventData;

class CorsightEndpointController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __invoke  (1.0) Handle the incoming webhook request from the Fortify
    //                 listener daemon. Signature verification and device
    //                 resolution live in EndpointRequest (authorize,
    //                 device); this action dispatches the persist job and
    //                 acknowledges so the daemon can dequeue it.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __invoke
     * <Function> Handle the incoming webhook request from the Fortify
     *            listener daemon. Verifies the HMAC signature over the raw
     *            body, resolves the device by the camera label (Fortify
     *            camera_description matched against the device name, may be
     *            null) and dispatches the queued job that persists the
     *            event, then acknowledges so the daemon can dequeue it.
     *
     * @param  \App\Http\Requests\Api\Corsight\EndpointRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(EndpointRequest $request)
    {
        HandleCorsightEventData::dispatch(
            $request->device(),
            $request->event(),
            $request->eventId(),
            $request->eventType(),
            $request->cameraId(),
        );

        return response()->noContent();
    }
}
