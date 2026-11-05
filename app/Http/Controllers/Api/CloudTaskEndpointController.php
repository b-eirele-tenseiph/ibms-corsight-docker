<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CloudTask\EndpointRequest;
use Illuminate\Http\JsonResponse;

class CloudTaskEndpointController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __invoke         (1.0) Handle the incoming request.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __invoke
     * <Function> Handle the incoming request.
     *
     * @param  \App\Http\Requests\Api\CloudTask\EndpointRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(EndpointRequest $request): JsonResponse
    {
        $job = $request->job();

        try {
            if (isset($job)) {
                // Process job
                dispatch_sync($request->job());
            }
        } catch (\Exception $exception) {
            return response()->json([], 204);
        }


        return response()->json([
            'status' => 'Job handled successfully'
        ], 200);
    }
}
