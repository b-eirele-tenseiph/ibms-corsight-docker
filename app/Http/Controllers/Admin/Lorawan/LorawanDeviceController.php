<?php

namespace App\Http\Controllers\Admin\Lorawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\LorawanDevice\{StoreRequest, UpdateRequest, SettingsRequest, CommandRequest};
use App\Jobs\Lorawan\{SaveDeviceCommand, SendDeviceCommand};
use App\Models\{LorawanDevice, LorawanGateway, Manufacturer, Room, RoomArea, Floor, ManufacturerModel};
use Illuminate\Http\{RedirectResponse, Request};
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Services\Lorawan\LorawanDeviceService;
use Illuminate\Http\{JsonResponse, Response};
use Illuminate\Support\Facades\Bus;

class LorawanDeviceController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct        (1.0) Inject required services.
    // index              (2.0) Retrieve list of lorawan devices from storage.
    // store              (3.0) Store a newly created resource in storage.
    // update             (4.0) Update the specified resource in storage.
    // destroy            (5.0) Remove the specified resource from storage.
    // command            (6.0) Send a command to the device.
    // settings           (7.0) Retrieve additional settings of selected device.
    // saveSettings       (8.0) Save configured settings of device.
    // export             (9.0) Export all lorawan Devices to Excel.
    // import             (10.0) Import lorawan Devices from Excel.
    // getTemplate        (11.0) Download template to Excel.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Inject required services
     *
     * @param LorawanDeviceService $deviceService
     */
    public function __construct(protected LorawanDeviceService $deviceService) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve list of lorawan devices from storage
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $devices = $this->deviceService->getFilteredDevices($request);

        return inertia('admin/lorawan-devices/Index', [
            'devices' => $devices,
            'models' => ManufacturerModel::all(),
            'filters' => (object) $request->query(),
            'gateways' => LorawanGateway::select('id', 'ip_address')->get(),
            'rooms' => Room::select('id', 'name', 'floor_id')->get(),
            'roomAreas' => RoomArea::select('id', 'name', 'room_id')->get(),
            'manufacturers' => Manufacturer::with('models')->get(),
            'floors' => Floor::select('id', 'name')->get(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\LorawanDevice\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        LorawanDevice::create($request->computed());

        return redirect()
            ->route('admin.management.lorawan.devices.index')
            ->with(Message::success(
                __('flash.added', ['attrib' => 'Device'])
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\LorawanDevice\UpdateRequest $request
     * @param  \App\Models\LorawanDevice                      $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, LorawanDevice $device): RedirectResponse
    {
        $device->update($request->computed());

        return redirect()
            ->back()
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'Device'])
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified resource from storage.
     *
     * @param  \App\Models\LorawanDevice  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(LorawanDevice $device): RedirectResponse
    {
        // Remove the specified resource held by $device
        $device->delete();

        // Redirect to the index method
        return back()
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'Device'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> command
     * <Function> Send a command to the device.
     *
     * @param  \App\Http\Requests\LorawanDevice\CommandRequest $request
     * @param  \App\Models\LorawanDevice                       $device
     * @return \Illuminate\Http\Response
     */
    public function command(CommandRequest $request, LorawanDevice $device): Response
    {
        Bus::chain([
            new SendDeviceCommand($device, $request->validated()),
            new SaveDeviceCommand($device, $request->validated()),
        ])->dispatch();

        return response()->noContent();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> settings
     * <Function> Retrieve additional settings of selected device.
     *
     * @param  \App\Models\LorawanDevice     $device
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings(LorawanDevice $device): JsonResponse
    {
        return response()->json([
            'enable_data_storage' => (bool) $device->enable_data_storage,
            'settings' => $device->settings
        ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> saveSettings
     * <Function> Save configured settings of device.
     *
     * @param  \App\Http\Requests\LorawanDevice\SettingsRequest $request
     * @param  \App\Models\LorawanDevice                        $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(SettingsRequest $request, LorawanDevice $device): RedirectResponse
    {
        $device->update([
            'enable_data_storage' => $request->validated()['enable_data_storage'],
            'context' => $request->computed()
        ]);

        return back()->with(Message::success(
            __('flash.updated', ['attrib' => 'Device'])
        ));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> export
     * <Function> Export all lorawan Devices to Excel.
     */
    public function export()
    {
        return $this->deviceService->export();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> import
     * <Function> Import lorawan Devices from Excel.
     */
    public function import(Request $request)
    {
        return $this->deviceService->import($request);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getTemplate
     * <Function> Download template to Excel.
     */
    public function getTemplate()
    {
        return $this->deviceService->getTemplate();
    }
}
