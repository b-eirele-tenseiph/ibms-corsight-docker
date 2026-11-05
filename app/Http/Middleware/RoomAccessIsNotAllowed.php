<?php

namespace App\Http\Middleware;

use App\Services\FlashMessage\FlashMessageService;
use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomAccessIsNotAllowed
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle            (1.0) Handle an incoming request.
    // checkIfHasAccess  (2.0) Check if user has access on date of login.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !$this->checkIfHasAccess()) {

            // log the tenant out of the application
            auth()->logout();

            // flush the session data and regenerate the ID
            $request->session()->invalidate();

            // regenerate new session token
            $request->session()->regenerateToken();

            // redirect to tenant login view
            return redirect()->route('login')->with(
                FlashMessageService::error(
                    __('auth.user_no_room_access')
                )
            );
        }

        return $next($request);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> checkIfHasAccess
     * <Function> Check if user has access on date of login.
     *
     * @return int
     */
    private function checkIfHasAccess()
    {
        return auth()->user()->roomAccess()
            ->where(function ($query) {
                $query
                    ->where(function ($q) {
                        $q
                            ->where('start_date', '<=', now())
                            ->where('end_date', '>=', now());
                    })
                    ->orWhere('is_permanent', "=", true);
            })
            ->count();
    }
}
