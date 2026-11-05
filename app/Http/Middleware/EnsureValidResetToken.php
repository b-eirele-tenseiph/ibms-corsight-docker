<?php

namespace App\Http\Middleware;

use App\Models\AdminPasswordReset;
use Closure;
use Illuminate\Http\Request;

class EnsureValidResetToken
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handle            (1.0) Handle an incoming request.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handle
     * <Function> Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Find the token in your admin_password_resets table
        $resetRecord = AdminPasswordReset::where('reset_token', $request->route('token'))->first();

        if (!$resetRecord) {
            abort(400, 'Invalid or already used password reset token.');
        }

        // Check the reset_token_expires_at column from your migration!
        if ($resetRecord->reset_token_expires_at->isPast()) {
            abort(410, 'Password reset token has expired. Please request a new one.');
        }

        return $next($request);
    }
}
