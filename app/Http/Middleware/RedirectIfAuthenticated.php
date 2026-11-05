<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated as RedirectIfAuthenticatedBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated extends RedirectIfAuthenticatedBase
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle           (1.0) Handle an incoming request.
    // getAuthGuard     (2.0) Get the guard of the authenticated user.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              ...$guards
     * @return void
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        if ($this->getAuthGuard() === 'admin') {
            return redirect(RouteServiceProvider::getAdminHome());
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::getHome());
            }
        }

        return $next($request);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getAuthGuard
     * <Function> Get the guard of the authenticated user.
     *
     * @return string|null
     */
    protected function getAuthGuard(): ?string
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (auth($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }
}
