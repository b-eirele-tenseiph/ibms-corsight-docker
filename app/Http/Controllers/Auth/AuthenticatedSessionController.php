<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Services\ActivityLog\CheckInOutLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // create       (1.0) Display the login view.
    // store        (2.0) Handle an incoming authentication request.
    // destroy      (3.0) Destroy an authenticated session.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> create
     * <Function> Display the login view.
     *
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/user/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('message'),
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> store
     * <Function> Handle an incoming authentication request.
     *
     */
    public function store(LoginRequest $request, CheckInOutLogService $checkInOutService,): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $checkInOutService->handleCheckInOut('in', $request->user());

        return redirect()->intended(RouteServiceProvider::getHome());
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> destroy
     * <Function> Destroy an authenticated session.
     *
     */
    public function destroy(Request $request, CheckInOutLogService $checkInOutService): RedirectResponse
    {
        $checkInOutService->handleCheckInOut('out', $request->user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
