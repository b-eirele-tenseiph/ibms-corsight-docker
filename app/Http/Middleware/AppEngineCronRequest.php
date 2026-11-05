<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AppEngineCronRequest
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
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): mixed
    {
        abort_unless($request->header('X-Appengine-Cron'), 403, 'Forbidden request.');

        return $next($request);
    }
}
