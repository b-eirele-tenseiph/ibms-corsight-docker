<?php

namespace App\Http\Controllers\Staff\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomAccess\StoreRequest;
use App\Http\Requests\RoomAccess\UpdateRequest;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomAccess;
use App\Services\RoomAccess\RoomAccessService;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Services\UserAccount\UserAccountService;

class DashboardController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct   (1.0) The constructor method.
    // index         (2.0) Return page with list of rooms and filters.
    // show          (3.0) Return data of a selected room.
    // store         (4.0) Save new room access record to storage via service.
    // update        (5.0) Update the room access and related records via
    //                     service.
    // destroy       (6.0) Delete the room access and related records via
    //                     service.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> The constructor method
     *
     * @param  \App\Services\RoomAccess\RoomAccessService $service
     * @param  \App\Services\UserAccount\UserAccountService $userAccountService
     */
    public function __construct(
        protected RoomAccessService $service,
        protected UserAccountService $userAccountService
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Return page with list of rooms and filters.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $rooms = $this->service->accesses($request);
        $filters = $request->query();
        $queryParams = $request->query();

        $floors = Floor::all();
        $external_apps = [];

        return inertia('staff/dashboard/Index', [
            'rooms' => $rooms,
            'queryParams' => (object) $queryParams,
            'filters' => (object) $filters,
            'floors' => $floors,
            'external_apps' => $external_apps,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> show
     * <Function> Return data of a selected room.
     *
     * @param  \Illuminate\Http\Request      $request
     * @param  \App\Models\Room              $room
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Room $room): JsonResponse
    {
        return response()->json(
            $this->service->loadRoom($room, $request->query())
        );
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> store
     * <Function> Save new room access record to storage via service.
     *
     * @param  \App\Http\Requests\RoomAccess\StoreRequest $request
     * @param  \App\Models\Room                           $room
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request, Room $room): RedirectResponse
    {
        $this->userAccountService->createUser(
            $request->userData(),
            $request->roomAccessData(),
            $request->switchbotLockAccessData(),
        );

        // Redirect to index method
        return redirect()
            ->route('staff.dashboard.index')
            ->with(Message::success(
                __('flash.added', ['attrib' => 'Room Access'])
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> update
     * <Function> Update the room access and related records via service.
     *
     * @param  \App\Http\Requests\RoomAccess\UpdateRequest $request
     * @param  \App\Models\RoomAccess                      $access
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, RoomAccess $access): RedirectResponse
    {
        $user = $access->user;
        $this->userAccountService->updateUser(
            $request->userData(),
            $user,
            $request->roomAccessData(),
            $request->switchbotLockAccessData(),
        );

        // Redirect to index method
        return redirect()
            ->route('staff.dashboard.index')
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'Room Access'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> destroy
     * <Function> Delete the room access and related records via service.
     *
     * @param  \App\Models\RoomAccess            $access
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(RoomAccess $access): RedirectResponse
    {
        $user = $access->user;
        $this->userAccountService->deleteUser($user);

        // Redirect to the index method
        return redirect()->route('staff.dashboard.index')
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'Room Access'])
            ));
    }
}
