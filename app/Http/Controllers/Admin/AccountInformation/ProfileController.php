<?php

namespace App\Http\Controllers\Admin\AccountInformation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // edit                 (1.0) Show the user's profile settings page.
    // update               (2.0) Update the user's profile information.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> edit
     * <Function> Show the user's profile settings page.
     *
     * @return \Inertia\Response
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('admin/account-profile/Index', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> update
     * <Function> Update the user's profile information.
     *
     * @param \App\Http\Requests\Settings\ProfileUpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()
            ->route('admin.settings.profile-edit')
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'user profile'])
            ));;
    }
}
