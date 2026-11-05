<?php

namespace App\Http\Controllers\Admin\SwitchBot;

use App\Models\{SwitchBotAccount, Room};
use App\Http\Requests\SwitchBotAccount\{StoreRequest, UpdateRequest};
use App\Http\Controllers\Controller;
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Services\SwitchBot\SwitchBotAccountService;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Inertia\Response;

class SwitchBotAccountController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __constructor        (1.0)  Constructor for initializing SwitchBotAccount
    //                             Service
    // index                (2.0)  Retrieve list of switch bot accounts from
    //                             storage.
    // update               (3.0)  Update the specified resource in storage.
    // store                (4.0)  Store a newly created resource in storage.
    // destroy              (5.0)  Deletes specified resource.
    // getAuthentication    (6.0)  Get the access token and secret key from
    // Credentials                 switch bot account.
    // getWebhook           (7.0)  Retrieve webhook of switch bot account.
    // import               (8.0)  Handle the import of SwitchBot accounts
    //                             from an uploaded Excel/CSV file.
    // export               (9.0)  Export all SwitchBot accounts into
    //                             an Excel file.
    // generateTemplate     (10.0) Generate and download an Excel template
    //                             for importing SwitchBot accounts.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing SwitchBotAccountService
     *
     */
    public function __construct(
        protected SwitchBotAccountService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve list of switchbot accounts from storage
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia('admin/switch-bot-account/Index', [
            'accounts' => $this->service->getAccounts($request),
            'filters' => $request->query(),
            'rooms' => Room::select('id', 'name', 'floor_id')->get(),
            'unlinked_rooms' => Room::doesntHave('switchBotAccount')->select('id', 'name', 'floor_id')->get(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\SwitchBotAccount\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->storeAccount($request);

        // Redirect to index method
        return back()->with(Message::success(
            __('flash.added', ['attrib' => 'Account'])
        ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @param \App\Http\Requests\SwitchBotAccount\UpdateRequest $request
     * @param \App\Models\SwitchBotAccount $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, SwitchBotAccount $account): RedirectResponse
    {
        $this->service->updateAccount($request, $account);

        // Redirect to index method
        return back()->with(Message::success(
            __('flash.updated', ['attrib' => 'Account'])
        ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified resource from storage.
     *
     * @param \App\Models\SwitchBotAccount $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SwitchBotAccount $account): RedirectResponse
    {
        $account->delete();

        return back()
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'Account'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getAuthenticationCredentials
     * <Function> Get the access token, secret key and password from switch bot account.
     *
     * @param \App\Models\SwitchBotAccount $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticationCredentials(SwitchBotAccount $account): JsonResponse
    {
        return $this->service->getAuthCredentials($account);
    }


    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getWebhook
     * <Function> Retrieve webhook of switch bot account.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\SwitchBotAccount  $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWebhook(Request $request, SwitchBotAccount $account): JsonResponse
    {
        return $this->service->getAccountWebhook($request, $account);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> import
     * <Function> Handle the import of SwitchBot accounts
     *            from an uploaded Excel/CSV file.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        return $this->service->importAccount($request);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> export
     * <Function> Export all SwitchBot accounts into
     *            an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return $this->service->exportAccount();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> generateTemplate
     * <Function> Generate and download an Excel template
     *            for importing SwitchBot accounts.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateTemplate()
    {
        return $this->service->generateTemplateAccount();
    }
}
