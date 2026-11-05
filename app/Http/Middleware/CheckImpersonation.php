<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class CheckImpersonation
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $handle          (1.0) Get the designated guards.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && session()->has('impersonating_role')) {

            // Skip role swap on impersonation routes so super admin check still passes
            if ($request->routeIs('admin.management.impersonate.*')) {
                return $next($request);
            }

            $user = $request->user();
            $fakeRole = session()->get('impersonating_role');

            // Save original role once before swapping
            if (!session()->has('original_role')) {
                session()->put('original_role', $user->roles->first()?->name);
            }

            // Clear stale cached permissions
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $role = Role::where('name', $fakeRole)
                ->where('guard_name', 'admin')
                ->with('permissions')
                ->first();

            if ($role) {
                // Swap role in memory only, not in the database
                $user->setRelation('roles', collect([$role]));
                $user->setRelation('permissions', $role->permissions);
            }
        }

        return $next($request);
    }
}
