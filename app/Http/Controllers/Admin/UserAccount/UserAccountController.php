<?php

namespace App\Http\Controllers\Admin\UserAccount;

use App\Http\Controllers\Controller;
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\StoreRequest;
use App\Models\User;
use App\Services\UserAccount\UserAccountService;
use Inertia\Inertia;
use Illuminate\Http\Request;

class UserAccountController extends Controller
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // __construct          (1.0) Delegate Services across controller
    // index                (2.0) Display the user management page along with
    //                            paginated user records.
    // store                (3.0) Create a new user and store to database.
    // update               (4.0) Update the specified user in database.
    // destroy              (5.0) Remove the specified user from database.
    // export               (6.0) Export all Users.


    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Delegate Services across controller
     *
     * @param \App\Services\UserAccount\UserAccountService $userAccountService
     *
     * @return \Inertia\Response
     */
    public function __construct(
        protected UserAccountService $userAccountService,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display the user management page along with paginated
     *            user records.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        return Inertia::render(
            'admin/user-account/Index',
            [
                'users'       => $this->userAccountService->getUsers($request->query()),
                'rooms'       => $this->userAccountService->getRooms(),
                'filters' => $request->query()
            ]
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Create a new user and store to database.
     *
     * @param \App\Http\Requests\User\StoreRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request)
    {
        $this->userAccountService->createUser(
            $request->userData(),
            $request->roomAccessData(),
            $request->switchbotLockAccessData(),
        );

        return back()
            ->with(Message::success(
                __(
                    'flash.added',
                    ['attrib' => 'User Account']
                )
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified user in database.
     *
     * @param  \App\Http\Requests\User\UpdateRequest  $request
     * @param  \App\Models\User  $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, User $user)
    {
        $this->userAccountService->updateUser(
            $request->userData(),
            $user,
            $request->roomAccessData(),
            $request->switchbotLockAccessData(),
        );

        return back()
            ->with(Message::success(
                __(
                    'flash.updated',
                    ['attrib' => 'User Account']
                )
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified user from database.
     *
     * @param  \App\Models\User  $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        $this->userAccountService->deleteUser($user);

        return back()
            ->with(Message::success(
                __(
                    'flash.deleted',
                    ['attrib' => 'User Account']
                )
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> export
     * <Function> Export all Users.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        return $this->userAccountService->exportUsers($request->all());
    }
}
