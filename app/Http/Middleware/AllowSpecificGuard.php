<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowSpecificGuard
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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $guard)
    {
        abort_if($guard !== $this->getAuthGuard(), 403);

        return $next($request);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getAuthGuard
     * <Function> Get the guard of the authenticated user.
     *
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
