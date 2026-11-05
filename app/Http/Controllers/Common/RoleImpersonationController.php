<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleImpersonation\StoreRequest;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RoleImpersonationController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getRole           (1.0) Retrieve the list of roles that have the admin
    //                         guard.
    // store             (2.0) Store the selected role into session and redirect
    //                         to their corresponding screen.
    // destroy           (3.0) Forget the impersonation role session

    /**
     *
     * <Layer number> (1.0)
     *
     * <Processing name> getRole
     * <Function> Retrieve the list of roles that have the admin guard.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRole()
    {
        return Role::where('guard_name', 'admin')
            ->whereNot('name', 'super admin')
            ->pluck('name');
    }

    /**
     *
     * <Layer number> (2.0)
     *
     * <Processing name> store
     * <Function> Store the selected role into session and redirect to their corresponding screen.
     *
     * @param \App\Http\Requests\RoleImpersonation\StoreRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreRequest $request): Response
    {
        $role = $request->validated('role');

        session(['impersonating_role' => $role]);

        return inertia()->location(route($role . '.dashboard.index' ?? 'admin.dashboard.index'));
    }

    /**
     *
     * <Layer number> (3.0)
     *
     * <Processing name> destroy
     * <Function> Forget the impersonation role session.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(): Response
    {
        session()->forget('impersonating_role');

        return inertia()->location(route('admin.dashboard.index'));
    }
}
