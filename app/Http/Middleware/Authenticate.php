<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as AuthenticateBase;
use Illuminate\Support\Arr;

class Authenticate extends AuthenticateBase
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guards      (1.0) Get the designated guards.
    // handle       (2.0) Get the guard of the authenticated user.
    // redirectTo   (3.0) Get the path the user should be redirected to when they
    //                    are not authenticated.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guards
     * <Function> Get the designated guards.
     *
     * @var string[]
     */
    protected $guards;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handle
     * <Function> Retrieve set guards then pass process to parent's handle
     *            method
     *
     * @param  \Illuminate\Http\Request   $request
     * @param  \Closure $next
     * @param  string[]                   ...$guards
     * @return void
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->guards = $guards;

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> redirectTo
     * <Function> Get the path the user should be redirected to when they are not
     *            authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            // Check if route has admin guard, if so redirect to admin login
            if (Arr::first($this->guards) === 'admin') {
                return route('admin.login');
            }
            return route('login');
        }
    }
}
