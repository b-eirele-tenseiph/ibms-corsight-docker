<?php

namespace App\Services\PasswordReset;

use App\Models\Activity;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use App\Traits\ActivityLog\LogsAudit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PasswordResetService
{
    use LogsAudit;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // MESSAGE_CODE_01            (1.0) Log code for password reset completion.
    // MESSAGE_CODE_02            (2.0) Log code for reset link generation.
    // resetPassword              (3.0) Handle the password update transaction.
    // generateResetPasswordLink  (4.0) Create token and generate a signed URL.
    // logActivity                (5.0) Private helper to record audit logs.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> MESSAGE_CODE_01
     * <Function> Constant string for password reset completion log code.
     *
     * @var string
     */
    protected const MESSAGE_CODE_01 = 'A002';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> MESSAGE_CODE_02
     * <Function> Constant string for reset link generation log code.
     *
     * @var string
     */
    protected const MESSAGE_CODE_02 = 'A003';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> resetPassword
     * <Function> Handle the password update transaction and invalidate token.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Models\Admin
     */
    public function resetPassword($request)
    {
        return DB::transaction(function () use ($request) {
            // Ensure we are getting the token from the request body
            $token = $request->input('token');

            $resetRecord = AdminPasswordReset::where('reset_token', $token)
                ->where('reset_token_expires_at', '>', now())
                ->first();

            $admin = Admin::find($resetRecord->admin_id);

            $admin->update([
                'password' => Hash::make($request->password),
                'password_last_updated_at' => now(),
            ]);

            $this->logActivity($admin, [
                'message' => ['user-admin' => $admin->name],
                'code' => self::MESSAGE_CODE_01
            ]);

            // Use the primary key (admin_id) to delete
            AdminPasswordReset::where('admin_id', $resetRecord->admin_id)->delete();

            return $admin;
        });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> generateResetPasswordLink
     * <Function> Create a secure reset token and generate a temporary signed URL.
     *
     * @param  \App\Models\Admin $admin
     * @return string
     */
    public function generateResetPasswordLink(Admin $admin)
    {
        $resetToken = Str::random(40);
        $expiresAt = now()->addMinutes(20);

        $admin->passwordResets()->updateOrCreate(
            ['admin_id' => $admin->id],
            [
                'reset_token' => $resetToken,
                'reset_token_expires_at' => $expiresAt
            ]
        );

        $this->logActivity($admin, [
            'message' => [
                'user-admin' => $admin->name,
                'super-admin' => auth()->user()->name
            ],
            'code' => self::MESSAGE_CODE_02
        ]);

        return URL::temporarySignedRoute('password.reset', $expiresAt, ['token' => $resetToken]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> logActivity
     * <Function> Private helper to format and record the audit log entry.
     *
     * @param  \App\Models\Admin $admin
     * @param  array $data
     * @return void
     */
    private function logActivity(Admin $admin, array $data = [])
    {
        $this->saveActivityLog(
            [
                'code' => $data['code'],
                'message' => get_message(Activity::TYPE_AUDIT, $data['code'], $data['message']),
                'performedOn' => $admin,
            ]
        );
    }
}
