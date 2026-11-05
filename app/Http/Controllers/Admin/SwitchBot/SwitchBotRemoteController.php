<?php

namespace App\Http\Controllers\Admin\SwitchBot;

use App\Http\Controllers\Controller;
use App\Http\Requests\SwitchBotRemote\{StoreRequest, UpdateRequest, SettingsRequest, CommandRequest};
use App\Models\{SwitchBotRemote, Room};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Services\SwitchBotRemote\SyncRemoteService;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SwitchBot\SendRemoteCommand;
use App\Services\SwitchBotRemote\SwitchBotRemoteService;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class SwitchBotRemoteController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct      (1.0) Injects the switchBotRemoteService for use in
    //                        retrieving remotes data.
    // index            (2.0) Retrieve list of switchbot remotes from storage.
    // store            (3.0) Store remotes base on scanned results.
    // update           (4.0) Update the specified resource in storage.
    // synchronize      (5.0) Scan SwitchBot Accounts' remotes.
    // command          (6.0) Send a command to the specified resource.
    // updateSettings   (7.0) Update device settings.
    // export           (8.0) Generates and returns downloadable export file.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Injects the switchBotRemoteService for use in retrieving
     *            remotes data.
     *
     * @param \App\Services\SwitchBotRemote\SwitchBotRemoteService $switchBotRemoteService
     */
    public function __construct(
        protected SwitchBotRemoteService $switchBotRemoteService
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve list of switchbot remotes from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia('admin/switch-bot-remote/Index', [
            'remotes' => $this->switchBotRemoteService->getRemotes($request),
            'filters'  => $request->query(),
            'rooms' => Room::select('id', 'name', 'floor_id')
                ->with(['roomAreas:id,name,room_id'])
                ->get(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store remotes base on scanned results.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        // Call the service to process creation, deletion, update, and initial data for remotes
        $this->switchBotRemoteService->storeRemotes(
            $request->remotesForCreation(),
            $request->remotesForDeletion(),
            $request->remotesForUpdate()
        );

        // Redirect back with success message
        return redirect()
            ->route('admin.management.switchbot.remotes.index')
            ->with(Message::success(__('flash.synced')));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\SwitchBotRemote  $remote
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, SwitchBotRemote $remote): RedirectResponse
    {
        $remote->update($request->validated());

        return redirect()
            ->back()
            ->with(Message::success(__('flash.updated', ['attrib' => 'Remote'])));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> synchronize
     * <Function> Scan SwitchBot Accounts' remotes.
     *
     * @param  SyncRemoteService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(SyncRemoteService $service): JsonResponse
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
     * <Layer number> (6.0)
     *
     * <Processing name> command
     * <Function> Send a command to the specified resource.
     *
     * @param  \App\Http\Requests\SwitchBotRemote\CommandRequest $request
     * @param  \App\Models\SwitchBotRemote                       $remote
     * @return \Illuminate\Http\Response
     */
    public function command(CommandRequest $request, SwitchBotRemote $remote): HttpResponse
    {
        $remote->load('hub.room.switchBotAccount');

        Cache::add("sbremotecontrol-{$remote->id}", $request->newData()['powerState'], 65); // with allowance

        SendRemoteCommand::dispatch($remote, $request->payload(), $request->newData());

        return response()->noContent();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> updateSettings
     * <Function> Update device settings.
     *
     * @param \App\Http\Requests\SwitchBotRemote\SettingsRequest
     * @param \App\Models\SwitchBotRemote $device
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(SettingsRequest $request, SwitchBotRemote $remote): RedirectResponse
    {
        $remote->update($request->validated());

        return back()->with(Message::success(
            __('flash.updated', ['attrib' => 'Remote'])
        ));
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> export
     * <Function> Generates and returns downloadable export file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): BinaryFileResponse
    {
        return $this->switchBotRemoteService->getExport();
    }
}
