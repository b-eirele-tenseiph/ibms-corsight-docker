<?php

namespace App\Http\Controllers\Admin\Corsight;

use App\Http\Controllers\Controller;
use App\Models\CorsightPersonSession;
use App\Services\Corsight\CorsightPersonService;
use Illuminate\Http\{JsonResponse, Request};
use Inertia\Response;

class CorsightPersonController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __constructor        (1.0)  Constructor for initializing
    //                             CorsightPersonService
    // index                (2.0)  Retrieve list of person sessions (people
    //                             enter/exit records) with filters.
    // show                 (3.0)  Retrieve a single session with its diary
    //                             trail.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing CorsightPersonService
     *
     */
    public function __construct(
        protected CorsightPersonService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve list of person sessions (people enter/exit
     *            records) with page-header counters and the device filter
     *            list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia('admin/corsight-person/Index', [
            'people' => $this->service->getPeople($request),
            'counters' => $this->service->getCounters($request),
            'devices' => $this->service->getDevices(),
            'filters' => $request->query(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> show
     * <Function> Retrieve a single session with its diary trail (the
     *            appearance events recorded for the visit).
     *
     * @param \App\Models\CorsightPersonSession $session
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(CorsightPersonSession $session): JsonResponse
    {
        return response()->json($this->service->getDetail($session));
    }
}
