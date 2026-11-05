<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\WaterBillingReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class WaterBillingReportController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct             (1.0) Initialize the controller with the
    //                               WaterBillingReportService dependency.
    // index                   (2.0) Render the water billing report index page.
    // getWaterRateData        (3.0) Return water rate data for a given

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initialize the controller with the WaterBillingReportService dependency.
     *
     * @param \App\Services\Report\WaterBillingReportService $service
     */
    public function __construct(
        protected WaterBillingReportService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Render the water billing report index page.
     *
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        return inertia('admin/water-billing-report/Index');
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getWaterRateData
     * <Function> Return water rate data for a given class.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWaterRateData(Request $request): JsonResponse
    {
        $class = $request->input('class', 'residential');

        return response()->json(
            $this->service->waterRateData($class)
        );
    }
}
