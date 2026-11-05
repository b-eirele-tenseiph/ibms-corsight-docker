<?php

namespace App\Http\Controllers\Admin\Corsight;

use App\Http\Controllers\Controller;
use App\Http\Requests\CorsightAccount\{StoreRequest, UpdateRequest};
use App\Models\CorsightAccount;
use App\Services\Corsight\CorsightAccountService;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Inertia\Response;

class CorsightAccountController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __constructor        (1.0)  Constructor for initializing
    //                             CorsightAccountService
    // index                (2.0)  Retrieve list of corsight accounts from
    //                             storage.
    // store                (3.0)  Store a newly created resource in storage.
    // update               (4.0)  Update the specified resource in storage.
    // destroy              (5.0)  Deletes specified resource.
    // getCredentials       (6.0)  Get the decrypted credentials from the
    //                             corsight account.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing CorsightAccountService
     *
     */
    public function __construct(
        protected CorsightAccountService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve list of corsight accounts from storage
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia('admin/corsight-account/Index', [
            'accounts' => $this->service->getAccounts($request),
            'filters' => $request->query(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\CorsightAccount\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $this->service->storeAccount($request);

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
     * @param \App\Http\Requests\CorsightAccount\UpdateRequest $request
     * @param \App\Models\CorsightAccount $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, CorsightAccount $account): RedirectResponse
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
     * @param \App\Models\CorsightAccount $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CorsightAccount $account): RedirectResponse
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
     * <Processing name> getCredentials
     * <Function> Get the decrypted credentials from the corsight account.
     *
     * @param \App\Models\CorsightAccount $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCredentials(CorsightAccount $account): JsonResponse
    {
        return $this->service->getCredentials($account);
    }
}
