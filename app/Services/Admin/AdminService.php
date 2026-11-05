<?php

namespace App\Services\Admin;

use App\Models\Admin;
use Spatie\Permission\Models\Role;

class AdminService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getAdmins             (1.0) Retrieves a paginated list of gateways with
    //                             sorting, filtering, and search options.
    // getavailableRoles     (2.0) Retrieves all roles with an exemption of 
    //                             super admin.
    // saveAdmin             (3.0) Save admin to storage
    // updateAdmin           (4.0) Update admin from storage
    // removeAdmin           (5.0) Remove admin from storage
    // toggleActivation      (6.0) Toggle admin's access

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getAdmins
     * <Function> Retrieves a paginated list of admins with sorting, filtering, and search options.
     *
     * @param  array $queryParams
     * @return void
     */
    public function getAdmins(array $queryParams)
    {
        [
            $search,
            $roleFilter,
            $allowedSortFields,
            $sortField,
            $sortDirection
        ] = [
            @$queryParams['search'],
            @$queryParams['role'],
            ['name', 'username', 'role', 'created_at'],
            @$queryParams['sort'],
            @$queryParams['direction'] ?? 'desc'
        ];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        return Admin::notSuperAdmin()
            // apply search if present
            ->when(
                $search,
                fn($q) => $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%")
                    ;
                })
            )
            // apply role filter if present
            ->when(
                $roleFilter,
                fn($q, $role) => $q->whereHas('roles', fn($q) => $q->where('roles.id', $role))
            )
            // apply sort if present
            ->when($sortField, function ($query, $sortField) use ($sortDirection) {
                if ($sortField == 'role') {
                    $query = $query->withAggregate('roles as role', 'name');
                }
                return $query->orderBy($sortField, $sortDirection);
            })
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getAvailableRoles
     * <Function> Retrieves all roles with an exemption of super admin.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRoles()
    {
        return Role::where('name', '!=', 'super admin')
            ->where('guard_name', 'admin')
            ->get();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> saveAdmin
     * <Function> Save admin to storage
     *
     * @param  array  $adminData
     * @param  string $role
     * @return void
     */
    public function saveAdmin(array $adminData, string $role)
    {
        // Store the new created resource
        $admin = Admin::create($adminData);

        // Assign admin with role 
        $admin->assignRole($role);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> updateAdmin
     * <Function> Update admin from storage
     *
     * @param  \App\Models\Admin $admin
     * @param  array             $adminData
     * @param  string            $role
     * @return void
     */
    public function updateAdmin(Admin $admin, array $adminData, string $role)
    {
        // Updates the specified resource held by $admin
        $admin->update($adminData);

        // Assign admin with role 
        $admin->syncRoles($role);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> removeAdmin
     * <Function> Remove admin from storage
     *
     * @param  \App\Models\Admin $admin
     * @return void
     */
    public function removeAdmin(Admin $admin)
    {
        // Deletes the specified resource
        $admin->delete();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> toggleActivation
     * <Function> Toggle admin's access
     *
     * @param  \App\Models\Admin $admin
     * @return void
     */
    public function toggleActivation(Admin $admin)
    {
        // Updates the specified resource held by $admin
        $admin->update([
            'enabled' => !$admin->enabled,
        ]);
    }
}
