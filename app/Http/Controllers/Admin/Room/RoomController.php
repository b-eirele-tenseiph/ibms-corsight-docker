<?php

namespace App\Http\Controllers\Admin\Room;

use App\Http\Requests\Room\{StoreRequest, UpdateRequest};
use App\Models\{Room, Floor};
use App\Services\FlashMessage\FlashMessageService;
use App\Services\Room\RoomService;
use Illuminate\Http\{RedirectResponse, Request};
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoomController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct      (1.0) Constructor for initializing Room Service.
    // index            (2.0) Display the list of rooms with filters and
    //                        pagination.
    // store            (3.0) Store a new room and its areas.
    // update           (4.0) Update an existing room and its areas.
    // destroy          (5.0) Delete a room.
    // deleteFloor      (6.0) Remove the specified floor from storage.
    // template         (7.0) Generates and returns downloadable template file.
    // import           (8.0) Handles data import functionality.
    // export           (9.0) Generates and returns downloadable export file.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing Room Service.
     *
     */
    public function __construct(
        protected RoomService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display the list of rooms with filters and pagination.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $data = $this->service->getIndexData($request);

        return Inertia::render('admin/room/Index', $data);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a new room and its areas.
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->createRoom($request);

        return redirect()->back()->with(FlashMessageService::success(
            __('flash.added', ['attrib' => 'Room'])
        ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update an existing room and its areas.
     *
     * @param Room $room
     * @param UpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, Room $room): RedirectResponse
    {
        $this->service->updateRoom($room, $request);

        return redirect()->back()->with(FlashMessageService::success(
            __('flash.updated', ['attrib' => 'Room'])
        ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Delete a room.
     *
     * @param Room $room
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('admin.management.rooms.index')->with(FlashMessageService::success(
            __('flash.deleted', ['attrib' => 'Room'])
        ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> deleteFloor
     * <Function> Remove the specified floor from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteFloor($id): RedirectResponse
    {
        $floor = Floor::findOrFail($id);

        if ($floor->rooms()->exists())
            return back()->withErrors(['floor' => 'Cannot delete a floor with associated rooms.']);

        $floor->delete();

        return back()->with(FlashMessageService::success(__('flash.deleted', ['attrib' => 'Floor'])));
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> template
     * <Function> Generates and returns downloadable template file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function template(): BinaryFileResponse
    {
        return $this->service->getTemplate();
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> import
     * <Function> Handles data import functionality.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request): RedirectResponse
    {
        return $this->service->getImport($request);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> export
     * <Function> Generates and returns downloadable export file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): BinaryFileResponse
    {
        return $this->service->getExport();
    }
}
