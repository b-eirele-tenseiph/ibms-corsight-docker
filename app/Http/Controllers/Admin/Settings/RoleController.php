<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Services\Roles\RolesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use App\Services\FlashMessage\FlashMessageService as Message;

class RoleController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // RolesService injection     (1.0) Injects the RolesService for use in
    //                                  retrieving role and permission data.
    // index                      (2.0) List all roles from storage and retrieve
    //                                  permissions for lookup
    // store                      (3.0) Create new role and give permissions.
    // update                     (4.0) Update role and sync permissions.
    // destroy                    (5.0) Remove the specified resource from
    //                                  storage.

    /**
     * The RolesService instance.
     *
     * @var \App\Services\Roles\RolesService;
     */
    protected $rolesService;

    /**
     * RolesController constructor.
     *
     * <Layer number> (1.0)
     *
     * <Processing name> RolesService injection
     * <Function> Injects the RolesService for use in retrieving role and permission data.
     *
     * @param \App\Services\Roles\RolesService;
     */
    public function __construct(RolesService $rolesService)
    {
        $this->rolesService = $rolesService;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> List all roles from storage and retrieve permissions for
     *            lookup
     *
     * @return \Inertia\Response
     */
    public function index(Request $request, RolesService $rolesService): Response
    {
        $roles = $rolesService->getRolesWithFilters($request);
        $permissions = $rolesService->getAllPermissions();

        return inertia('admin/role/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'by', 'order']),
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Create new role and give permissions.
     *
     * @param  \App\Http\Requests\Role\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $newRole = Role::create($request->roleData());

        // Assign permissions to new role
        $newRole->givePermissionTo($request->permissionData());

        return redirect()
            ->route('admin.management.settings.roles.index')
            ->with(Message::success(
                __('flash.added', ['attrib' => 'Role'])
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update role and sync permissions.
     *
     * @param  \App\Http\Requests\Role\UpdateRequest $request
     * @param  \Spatie\Permission\Models\Role        $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, Role $role): RedirectResponse
    {
        $role->update($request->roleData());

        $role->syncPermissions($request->permissionData());

        return redirect()
            ->back()
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'Role'])
            ));
    }


    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified resource from storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(): RedirectResponse
    {

        Role::wherein('id', request('ids'))->delete();

        return redirect()
            ->route('admin.management.settings.roles.index')
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'Role/s'])
            ));
    }
}
