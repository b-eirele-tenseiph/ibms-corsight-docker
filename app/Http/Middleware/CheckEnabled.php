<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckEnabled
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle           (1.0) Handle an incoming request.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth('admin')->check() && (auth('admin')->user()->enabled == 0)) {

            // log the admin out of the application
            auth('admin')->logout();

            // flush the session data and regenerate the ID
            $request->session()->invalidate();

            // regenerate new session token
            $request->session()->regenerateToken();

            // redirect to admin login view
            return redirect()->route('admin.login')->withErrors([
                'status' => __('auth.admin_disabled')
            ]);
        }
        return $next($request);
    }
}
