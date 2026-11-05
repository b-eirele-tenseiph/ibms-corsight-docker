<?php

namespace App\Http\Controllers\Admin\SwitchBot;

use App\Jobs\SwitchBot\SendDeviceCommand;
use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchBotDevice\{StoreRequest, UpdateRequest, SettingsRequest, CommandRequest};
use App\Models\{Room, SwitchBotDevice};
use App\Services\FlashMessage\FlashMessageService;
use App\Services\SwitchBotDevice\{SwitchBotDeviceService, SyncDeviceService};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SwitchBotDeviceController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __constructor        (1.0) Constructor for initializing SwitchBotAccount
    //                            Service
    // index                (2.0) Retrieve list of switch bot device from
    //                            storage.
    // synchronize          (3.0) Scan SwitchBotDevices.
    // store                (4.0) Store a newly created resource in storage.
    // update               (5.0) Update the specified resource in storage.
    // command              (6.0) Send a command to the specified resource.
    // updateSettings       (7.0) Update device settings.
    // export               (8.0) Export all SwitchBot devices into an Excel
    //                            file.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing SwitchBotDeviceService
     *
     * @param  SwitchBotDeviceService $service
     * @return void
     */
    public function __construct(
        protected SwitchBotDeviceService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve list of switchbot device from storage
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia('admin/switch-bot-device/Index', [
            'devices'  => $this->service->getDevices($request),
            'filters'  => $request->query(),
            'rooms' => Room::select('id', 'name', 'floor_id')
                ->with(['roomAreas:id,name,room_id'])
                ->get(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> synchronize
     * <Function> Scan SwitchBotDevices.
     *
     * @param \App\Services\SwitchBotDevice\SyncDeviceService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function synchronize(SyncDeviceService $service): JsonResponse
    {
        try {
            // Call job to process synchronization
            $results = $service->handle();
        } catch (Throwable $ex) {
            report($ex);
            return response()->json(['error' => __('messages.error.E022.action')], 500);
        }

        return response()->json($results);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> store
     * <Function> Store scanned devices.
     *
     * @param \App\Http\Requests\SwitchBotDevice\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {

        $this->service->storeDevices($request);

        return redirect()
            ->route('admin.management.switchbot.devices.index')
            ->with(FlashMessageService::success(
                __('flash.synced')
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @param \App\Http\Requests\SwitchBotDevice\UpdateRequest $request
     * @param \App\Models\SwitchBotDevice $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, SwitchBotDevice $device): RedirectResponse
    {
        $device->update($request->validated());

        return redirect()
            ->back()
            ->with(FlashMessageService::success(
                __('flash.updated', ['attrib' => 'Device'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> command
     * <Function> Send a command to the specified resource.
     *
     * @param \App\Http\Requests\SwitchBotDevice\CommandRequest $request
     * @param \App\Models\SwitchBotDevice $device
     * @return \Illuminate\Http\Response
     */
    public function command(CommandRequest $request, SwitchBotDevice $device): HttpResponse
    {
        $device->load('room.switchBotAccount');

        Bus::chain([
            new SendDeviceCommand($device, $request->validated()),
            // function () {
            //     sleep(3);
            // },
            // (new FetchDeviceStatus($device))->delay(now()->addSeconds(10)),
        ])->dispatch();

        return response()->noContent();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> updateSettings
     * <Function> Update device settings.
     *
     * @param \App\Http\Requests\SwitchBotDevice\SettingsRequest
     * @param \App\Models\SwitchBotDevice $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(SettingsRequest $request, SwitchBotDevice $device): RedirectResponse
    {
        $device->update($request->validated());

        return back()->with(FlashMessageService::success(
            __('flash.updated', ['attrib' => 'Device'])
        ));
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> export
     * <Function> Export all SwitchBot devices into an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): BinaryFileResponse
    {
        return $this->service->exportDevices();
    }
}
