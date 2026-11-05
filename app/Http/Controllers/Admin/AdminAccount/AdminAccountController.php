<?php

namespace App\Http\Controllers\Admin\AdminAccount;

use App\Http\Requests\Admin\StoreRequest;
use App\Models\Admin;
use App\Services\Admin\AdminService;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRequest;
use App\Services\PasswordReset\PasswordResetService;
use Illuminate\Http\JsonResponse;

class AdminAccountController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct                      (1.0) Constructor to initialize the
    //                                        AdminService.
    // index                            (2.0) Retrieve list of switchbot
    //                                        accounts from storage.
    // store                            (3.0) Store a newly created resource
    //                                        in storage.
    // update                           (4.0) Update the specified resource
    //                                        in storage.
    // destroy                          (5.0) Deletes specified resource.
    // optionEnabled                    (6.0) Update the enabled status
    //                                        in storage.
    // generateResetPasswordLink        (7.0) Generate a password reset link
    //                                        for the admin.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor to initialize the AdminService.
     *
     * @param \App\Services\Admin\AdminService $service
     */
    public function __construct(
        protected AdminService $service
    ) {}

    /**
     * <Layer number> (layer)
     *
     * <Processing name> methodName
     * <Function> description
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $admins = $this->service->getAdmins($request->query());

        $roles  = $this->service->getAvailableRoles();

        $filters = $request->only('search', 'role');

        return inertia('admin/admin-account/Index', [
            'admins'     => $admins,
            'roles'      => $roles,
            'filters'    => $filters,
            'queryParams' => $request->query(),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     *
     * <Function> Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Admin\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request)
    {
        $this->service->saveAdmin($request->validatedAdmin(), $request->validatedRole());

        // Redirect to index method
        return back()
            ->with(Message::success(
                __('flash.added', ['attrib' => 'Admin Account'])
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\UpdateRequest $request
     * @param  \App\Models\Admin $admin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, Admin $admin)
    {
        $this->service->updateAdmin(
            $admin,
            $request->validatedAdmin(),
            $request->validatedRole()
        );

        // Redirect to index method
        return back()
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'Admin Account'])
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Deletes the specified resource.
     *
     * @param  \App\Models\Admin $admin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Admin $admin)
    {
        $this->service->removeAdmin($admin);

        // Redirect to previous page
        return back()
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'Admin Account'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> optionEnabled
     * <Function> Update the enabled status in storage.
     *
     * @param  \App\Models\Admin $admin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function optionEnabled(Admin $admin)
    {
        $this->service->toggleActivation($admin);

        // Redirect to index method
        return back()
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'Admin Account'])
            ));
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> generateResetPasswordLink
     * <Function> Generate a password reset link for the admin.
     *
     * @param  \App\Models\Admin $admin
     * @param  \App\Services\PasswordReset\PasswordResetService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateResetPasswordLink(Admin $admin, PasswordResetService $service): JsonResponse
    {
        // 1. Generate the link using your service
        $link = $service->generateResetPasswordLink($admin);

        // 2. Return it as a JSON object so Axios can read response.data.link
        return response()->json([
            'link' => $link
        ]);
    }
}
