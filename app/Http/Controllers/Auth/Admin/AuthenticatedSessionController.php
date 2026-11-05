<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // create           (1.0) Display the admin login view.
    // store            (2.0) Handle an incoming admin authentication request.
    // destroy          (3.0) Destroy an authenticated admin session.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> create
     * <Function> Display the admin login view.
     *
     */
    public function create(): Response
    {
        return inertia('auth/admin/Login', [
            'canResetPassword' => false,
            'status' => session('status'),
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> store
     * <Function> Handle an incoming admin authentication request.
     *
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::getAdminHome());
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> destroy
     * <Function> Destroy an authenticated admin session.
     *
     */
    public function destroy(Request $request): RedirectResponse
    {
        auth('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
