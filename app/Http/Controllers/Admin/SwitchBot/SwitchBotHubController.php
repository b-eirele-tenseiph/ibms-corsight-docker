<?php

namespace App\Http\Controllers\Admin\SwitchBot;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchBotHub\{StoreRequest, UpdateRequest};
use App\Models\{Room, SwitchBotHub};
use App\Services\FlashMessage\FlashMessageService;
use App\Services\SwitchBotHub\{SwitchBotHubService, SyncHubService};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SwitchBotHubController extends Controller
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // __construct          (1.0) Delegate Services across controller
    // index                (2.0) Display the user management page along with
    //                            paginated user records.
    // sync                 (3.0) Display the user management page along with
    //                            paginated user records.
    // store                (4.0) Create a new user and store to database.
    // update               (5.0) Update the specified user in database.
    // export               (6.0) Export all SwitchBot devices into
    //                            an Excel file.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Delegate Services across controller
     *
     * @param
     *
     * @return \Inertia\Response
     */
    public function __construct(protected SwitchBotHubService $switchBotHubService) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display the user management page along with paginated
     *            user records.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia(
            'admin/switch-bot-hub/Index',
            [
                'hubs' => $this->switchBotHubService->getSwitchBotHubs($request),
                'rooms' => Room::select('id', 'name', 'floor_id')
                    ->with(['roomAreas:id,name,room_id'])
                    ->get(),
                'filters' => $request->query(),
            ]
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> sync
     * <Function> Display the user management page along with paginated
     *            user records.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(SyncHubService $service): JsonResponse
    {
        try {
            // Call job to process synchronization
            $results = $service->handle();
        } catch (Throwable $throwable) {
            return response()->json(['error' => __('messages.error.E022.action')], 500);
        }

        return response()->json($results);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> store
     * <Function> Display the user management page along with paginated
     *            user records.
     *
     * @param \App\Http\Requests\SwitchBotHub\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->switchBotHubService->storeHubs($request);

        return redirect()
            ->route('admin.management.switchbot.hubs.index')
            ->with(FlashMessageService::success(
                __('flash.synced')
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> update
     * <Function> Display the user management page along with paginated
     *            user records.
     *
     * @param \App\Http\Requests\SwitchBotHub\UpdateRequest $request
     * @param \App\Models\SwitchBotHub $hub
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, SwitchBotHub $hub): RedirectResponse
    {
        $hub->update($request->validated());

        return redirect()
            ->back()
            ->with(FlashMessageService::success(
                __('flash.updated', ['attrib' => 'Hub'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> export
     * <Function> Export all SwitchBot Hubs into
     *            an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): BinaryFileResponse
    {
        return $this->switchBotHubService->exportHubs();
    }
}
