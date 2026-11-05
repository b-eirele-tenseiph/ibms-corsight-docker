<?php

namespace App\Http\Controllers\Admin\MonitoringAndControls;

use App\Http\Controllers\Controller;
use App\Services\MonitoringAndControls\MonitoringAndControlService;
use Illuminate\Http\Request;
use Inertia\Response;

class SwitchController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __invoke        (1.0) Retrieve list of light switch from storage.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __invoke
     * <Function> Retrieve list of light switch from storage.
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

        return inertia('admin/monitoring-switch/Index', [
            'switches' => $service->getSwitches($filters),
            'queryParams' => $request->query(),
        ]);
    }
}
