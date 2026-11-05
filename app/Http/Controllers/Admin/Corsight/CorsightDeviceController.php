<?php

namespace App\Http\Controllers\Admin\Corsight;

use App\Http\Controllers\Controller;
use App\Http\Requests\CorsightDevice\{SettingsRequest, StoreRequest, UpdateRequest};
use App\Models\{CorsightDevice, Room};
use App\Services\Corsight\CorsightDeviceService;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\{RedirectResponse, Request};
use Inertia\Response;

class CorsightDeviceController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __constructor        (1.0)  Constructor for initializing
    //                             CorsightDeviceService
    // index                (2.0)  Retrieve list of corsight devices from
    //                             storage.
    // store                (3.0)  Store a newly created resource in storage.
    // update               (4.0)  Update the specified resource in storage.
    // updateSettings       (5.0)  Update device settings.
    // destroy              (6.0)  Deletes specified resource.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing CorsightDeviceService
     *
     */
    public function __construct(
        protected CorsightDeviceService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve list of corsight devices from storage
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia('admin/corsight-device/Index', [
            'devices' => $this->service->getDevices($request),
            'filters' => $request->query(),
            'rooms' => Room::select('id', 'name', 'floor_id')
                ->with(['roomAreas:id,name,room_id'])
                ->get(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\CorsightDevice\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->storeDevice($request);

        // Redirect to index method
        return back()->with(Message::success(
            __('flash.added', ['attrib' => 'Device'])
        ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @param \App\Http\Requests\CorsightDevice\UpdateRequest $request
     * @param \App\Models\CorsightDevice $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, CorsightDevice $device): RedirectResponse
    {
        $this->service->updateDevice($request, $device);

        // Redirect to index method
        return back()->with(Message::success(
            __('flash.updated', ['attrib' => 'Device'])
        ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> updateSettings
     * <Function> Update device settings.
     *
     * @param \App\Http\Requests\CorsightDevice\SettingsRequest $request
     * @param \App\Models\CorsightDevice $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(SettingsRequest $request, CorsightDevice $device): RedirectResponse
    {
        $device->update($request->validated());

        return back()->with(Message::success(
            __('flash.updated', ['attrib' => 'Device'])
        ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified resource from storage.
     *
     * @param \App\Models\CorsightDevice $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CorsightDevice $device): RedirectResponse
    {
        $device->delete();

        return back()
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'Device'])
            ));
    }
}
