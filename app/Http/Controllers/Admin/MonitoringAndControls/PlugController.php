<?php

namespace App\Http\Controllers\Admin\MonitoringAndControls;

use App\Http\Controllers\Controller;
use App\Services\MonitoringAndControls\MonitoringAndControlService;
use Illuminate\Http\Request;
use Inertia\Response;

class PlugController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __invoke        (1.0) Retrieve list of plug devices from storage.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __invoke
     * <Function> Retrieve list of plug devices from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\MonitoringAndControls\MonitoringAndControlService $service
     * @return \Inertia\Response
     */
    public function __invoke(Request $request, MonitoringAndControlService $service): Response
    {
        // Extract filters and sorting options from the request
        $filters = $request->only(['search', 'sort', 'direction']) + [
            'sort' => 'name',
            'direction' => 'asc'
        ];

        return inertia('admin/monitoring-plug/Index', [
            'appliances' => $service->getPlugs($filters),
            'queryParams' => $request->query(),
        ]);
    }
}
