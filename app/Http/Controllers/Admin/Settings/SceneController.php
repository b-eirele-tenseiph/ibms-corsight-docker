<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scene\StoreRequest;
use App\Http\Requests\Scene\UpdateRequest;
use App\Models\Room;
use App\Models\Scene;
use App\Services\FlashMessage\FlashMessageService;
use App\Services\Scenes\ScenesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SceneController extends Controller
{

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $service                   (1.0) The ScenesService instance for handling
    //                                  scene-related operations.
    // __construct                (2.0) Injects the ScenesService for use in
    //                                  retrieving scene and room data.
    // index                      (3.0) Display a listing of scenes.
    // store                      (4.0) Store a newly created scene and
    //                                  associated devices.
    // update                     (5.0) Update an existing scene and its
    //                                  associated devices.
    // destroy                    (6.0) Delete a scene and its associated
    //                                  device records.
    // fetchDevices               (7.0) Fetch devices in a room.
    // executeCommand             (8.0) Execute a command on a given device
    //                                  based on its type.

    /**
     * <Layer Number> (1.0)
     *
     * <Processing Name> $service
     * <Function> The ScenesService instance for handling scene-related operations.
     *
     * @var \App\Services\Scenes\ScenesService
     */
    protected ScenesService $service;

    /**
     * <Layer Number> (2.0)
     *
     * <Processing Name> __construct
     * <Function> Injects the ScenesService for use in retrieving scene and room data.
     *
     * @param  \App\Services\Scenes\ScenesService $service
     * @return void
     */
    public function __construct(ScenesService $service)
    {
        $this->service = $service;
    }

    /**
     * <Layer Number> (3.0)
     *
     * <Processing Name> index
     * <Function> Display a listing of scenes.
     *
     */
    public function index(Request $request)
    {
        $filters = $request->query();

        $scenes = $this->service->getPaginatedScenes($filters);

        if ($scenes->currentPage() > $scenes->lastPage() && $scenes->lastPage() > 0) {
            return redirect()->route('admin.management.settings.scenes.index', [
                ...$filters,
                'page' => 1,
            ]);
        }

        return inertia('admin/scenes/Index', [
            'scenes'   => $scenes,
            'rooms'    => Room::all(),
            'filters'  => (object) $filters,
            'theme' => config('scenes'),
            'icon' => config('icons'),
        ]);
    }

    /**
     * <Layer Number> (4.0)
     *
     * <Processing Name> store
     * <Function> Store a newly created scene and associated devices.
     *
     * @param  \App\Http\Requests\Scene\StoreRequest $request
     * @param  \App\Services\Scenes\ScenesService         $scenesService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request)
    {
        $this->service->storeScene($request->computed());
        return back()->with(FlashMessageService::success(__('flash.added', ['attrib' => 'Scene'])));
    }

    /**
     * <Layer Number> (5.0)
     *
     * <Processing Name> update
     * <Function> Update an existing scene and its associated devices.
     *
     * @param  \App\Http\Requests\Scene\UpdateRequest $request
     * @param  \App\Models\Scene $scene
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, Scene $scene)
    {
        $this->service->updateScene($scene, $request->computed());
        return back()->with(FlashMessageService::success(__('flash.updated', ['attrib' => 'Scene'])));
    }

    /**
     * <Layer Number> (6.0)
     *
     * <Processing Name> destroy
     * <Function> Delete a scene and its associated device records.
     *
     * @param  \App\Models\Scene $scene
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Scene $scene): RedirectResponse
    {
        $scene->sceneDevices()->delete();
        $scene->delete();
        return back()->with(FlashMessageService::success(__('flash.deleted', ['attrib' => 'Scene'])));
    }

    /**
     * <Layer Number> (7.0)
     *
     * <Processing Name> fetchDevices
     * <Function> Fetch devices in a room.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDevices(Request $request)
    {
        $room = Room::findOrFail($request->input('room_id'));
        $devices = $this->service->getDevicesInRoom($room);
        return response()->json($devices);
    }

    /**
     * <Layer Number> (8.0)
     *
     * <Processing Name> executeCommand
     * <Function> Execute a command on a given device based on its type.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function executeCommand(Scene $scene): RedirectResponse
    {
        $hasError = $this->service->executeSceneCommands($scene);

        if (!$hasError) {
            return back()->with(FlashMessageService::success(__('flash.executed', ['attrib' => 'Scene'])));
        }

        return back()->with(FlashMessageService::error(__('flash.execute_failed', ['attrib' => 'Scene'])));
    }
}
