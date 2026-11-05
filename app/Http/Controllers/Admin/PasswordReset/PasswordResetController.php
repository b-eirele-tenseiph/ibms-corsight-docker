<?php

namespace App\Http\Controllers\Admin\PasswordReset;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PasswordReset\UpdateRequest;
use App\Services\PasswordReset\PasswordResetService;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // index           (1.0) Display the password reset view with the token.
    // update          (2.0) Handle the password update request.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> index
     * <Function> Display the password reset view with the token.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string $token
     * @return \Inertia\Response
     */
    public function index(Request $request, $token)
    {
        return inertia('auth/admin/PasswordResetLink', compact('token'));
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> update
     * <Function> Handle the password update request.
     *
     * @param  \App\Http\Requests\Admin\PasswordReset\UpdateRequest $request
     * @param  \App\Services\PasswordReset\PasswordResetService $service
     * @return \Inertia\Response
     */
    public function update(UpdateRequest $request, PasswordResetService $service)
    {
        $service->resetPassword($request);

        return inertia('auth/admin/PasswordResetLink', [
            "success" => true,
            "token" => $request->token,
        ]);
    }
}
