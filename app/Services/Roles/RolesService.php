<?php

namespace App\Services\Roles;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RolesService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getRolesWithFilters     (1.0)  Retrieve roles with optional filtering,
    //                                 sorting, and pagination.
    // getAllPermissions       (2.0)  Retrieve all permissions, sorted by latest.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getRolesWithFilters
     * <Function> Retrieve roles with filtering by search term, sortable by
     *            column, and paginated result. Excludes "super admin" role.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRolesWithFilters(Request $request)
    {
        return Role::with('permissions')
            ->where('name', '<>', 'super admin')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('guard_name', 'like', "%{$search}%")
                        ->orWhereHas('permissions', function ($permQuery) use ($search) {
                            $permQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->by, function ($query) use ($request) {
                $query->orderBy($request->by, $request->order === 'desc' ? 'desc' : 'asc');
            }, function ($query) {
                $query->oldest();
            })
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getAllPermissions
     * <Function> Retrieve all permissions sorted by latest created.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        return Permission::latest()->get();
    }
}
