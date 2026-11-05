<?php

namespace App\Http\Controllers\Admin\Logs;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\ActivityLog\ActivityLogsService;
use Illuminate\Http\Request;
use App\Services\Emergency\EmergencyService;
use Illuminate\Support\Facades\Auth;
use App\Services\FlashMessage\FlashMessageService as Message;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityLogsController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct       (1.0)  Constructor for initializing ActivityLogsService
    // index             (2.0)  Display activity logs page.
    // update            (3.0)  Update the specified resource in storage.
    // export            (4.0)  Generates and returns downloadable export file.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing ActivityLogsService
     *
     */
    public function __construct(
        protected ActivityLogsService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display activity logs page.
     *
     */
    public function index(Request $request)
    {
        if (!$request->has('date_from') || !$request->has('date_to')) {

            $activityLog = $this->service->getLogs($request);
            $firstLog = $activityLog->first();

            if ($firstLog) {
                $targetDate = $firstLog->created_at;
            } else {
                $targetDate = now();
            }

            $request->merge([
                'date_from' => $targetDate->startOfMonth()->toDateString(),
                'date_to'   => $targetDate->endOfMonth()->addDay()->toDateString(),
            ]);

            return redirect()->route('admin.management.logs.index', $request->query());
        }

        $logs = $this->service->getLogs($request);
        $filter = $this->service->getFilterOptions();

        return inertia('admin/logs/Index', [
            'logs' => $logs,
            'filter' => $filter,
            'queryParams' => $request->query()
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @return void
     */
    public function update(Request $request, Activity $error)
    {
        // Fetch current properties of selected log.
        $properties = $error->properties;

        // Retrieve room_id properly
        $roomId = $properties->get('room_id');

        if ($roomId) {
            // @@todo Trigger only turning off if it is needed like during emergency
            // Find sirens in the specified room and send the mute command
            EmergencyService::turnOffSiren($roomId);
        }

        // Update resolved_by attribute with currently logged in admin
        $properties['Resolved by'] = Auth::user()->name;

        // Save changes
        $error->update(compact('properties'));

        return back()->with(Message::success(__('flash.resolved')));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> export
     * <Function> Generates and returns downloadable export file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        return $this->service->getExport($request);
    }
}
