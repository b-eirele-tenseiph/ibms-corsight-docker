<?php

namespace App\Http\Controllers\Admin\AccountInformation;

use App\Http\Controllers\Controller;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // edit                 (1.0) Show the user's password settings page.
    // update               (2.0) Update the user's password.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> edit
     * <Function> Show the user's password settings page.
     *
     * @return \Inertia\Response
     */
    public function edit(): Response
    {
        return Inertia::render('admin/account-password/Index');
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> update
     * <Function> Update the user's password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'password'])
            ));
    }
}
