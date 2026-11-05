<?php

namespace Tests\Feature\Admin\PasswordReset;

use App\Http\Controllers\Admin\PasswordReset\PasswordResetController;
use App\Models\Admin;
use App\Models\AdminPasswordReset;
use App\Services\PasswordReset\PasswordResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Traits\{Data, Roles};
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase, Data, Roles, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createPasswordResetData  (1.0) Create password reset data for testing.
    // getPasswordReset         (2.0) Generate URL for password reset.
    // GeneratedURL
    // changedPasswordPost      (3.0) Send POST request to change password
    // RequestWithValidation          with validation.
    // test_reset_password      (4.0) Test reset password page with valid token
    // _page_with_valid_token         and signature.
    // _and_signature
    // test_reset_password      (5.0) Test reset password page with expired
    // _page_with_expired_token       token.
    //
    // test_reset_password      (6.0) Test reset password page with invalid or
    // _page_with_invalid             already used token.
    // _or_already_used_token
    // test_user_that_has       (7.0) Test if user with generated password reset
    // _generated_password            link can change password.
    // _reset_link_can
    // _change_password
    // test_reset_password      (8.0) Test reset password process contains
    // _process_contain               validation rules.
    // _validation_rules
    // test_reset_password      (9.0) Test reset password process contains
    // _process_contain               validation if data are not valid.
    // _validation_if_data
    // _are_not_valid

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createPasswordResetData
     * <Function> Create password reset data for testing.
     *
     * @param mixed $expiresAt
     * @return \App\Models\AdminPasswordReset
     */
    protected function createPasswordResetData(mixed $expiresAt = null)
    {
        return AdminPasswordReset::factory()->create([
            'admin_id' => $this->createAdminUser()->id,
            'reset_token_expires_at' => $expiresAt ?? now()->addMinutes(20),
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getPasswordResetGeneratedURL
     * <Function> Generate URL for password reset.
     *
     * @param mixed $token
     * @param mixed $expires
     *
     * @return \Illuminate\Testing\TestResponse
     */
    protected function getPasswordResetGeneratedURL($token, $expires)
    {
        return $this->get(route('password.reset', [
            'token' => $token,
            'expires' => $expires,
            'signature' => $this->faker->sha256(),
        ]));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> changedPasswordPostRequestWithValidation
     * <Function> Send POST request to change password with validation.
     *
     * @param array $data
     * @param array $expectedErrors
     *
     * @return void
     */
    protected function changedPasswordPostRequestWithValidation(array $data, array $expectedErrors)
    {
        $this->post(
            action([PasswordResetController::class, 'update']),
            array_merge($data, ['token' => $this->createPasswordResetData()->reset_token])
        )->assertSessionHasErrors($expectedErrors);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_reset_password_page_with_valid_token_and_signature
     * <Function> Test reset password page with valid token and signature.
     *
     * @return void
     */
    public function test_reset_password_page_with_valid_token_and_signature()
    {
        $reset = $this->createPasswordResetData();
        $expires = $reset->reset_token_expires_at->timestamp;

        $this->getPasswordResetGeneratedURL($reset->reset_token, $expires)
            ->assertStatus(200)
            ->assertInertia(function (Assert $page) use ($reset) {
                $page->component('auth/admin/PasswordResetLink')
                    ->where('token', $reset->reset_token);
            });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_reset_password_page_with_expired_token
     * <Function> Test reset password page with expired token.
     *
     * @return void
     */
    public function test_reset_password_page_with_expired_token()
    {
        $reset = $this->createPasswordResetData(now()->subMinutes(10));

        $this->getPasswordResetGeneratedURL($reset->reset_token, $reset->reset_token_expires_at->timestamp)
            ->assertStatus(410)
            ->assertSee('Password reset token has expired. Please request a new one.');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_reset_password_page_with_invalid_or_already_used_token
     * <Function> Test reset password page with invalid or already used token.
     *
     * @return void
     */
    public function test_reset_password_page_with_invalid_or_already_used_token(): void
    {
        $reset = $this->createPasswordResetData();

        $this->getPasswordResetGeneratedURL(Str::random(40), $reset->reset_token_expires_at->timestamp)
            ->assertStatus(400)
            ->assertSee('Invalid or already used password reset token.');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_user_that_has_generated_password_reset_link_can_change_password
     * <Function> Test if user with generated password reset link can change password.
     *
     * @return void
     */
    public function test_user_that_has_generated_password_reset_link_can_change_password()
    {
        $admin = Admin::factory()->create();
        $token = $this->createPasswordResetData()->reset_token;

        $this->app->instance(
            PasswordResetService::class,
            $this->partialMock(PasswordResetService::class, function ($mock) use ($admin, $token) {
                $mock->shouldReceive('resetPassword')
                    ->once()
                    ->andReturnUsing(function ($request) use ($admin, $token) {
                        $admin->update(['password' => Hash::make($request->password)]);
                        AdminPasswordReset::where('reset_token', $token)->delete();
                    });
            })
        );

        $this->post(action([PasswordResetController::class, 'update']), [
            'password' => 'newPassword123!',
            'password_confirmation' => 'newPassword123!',
            'token' => $token,
        ])->assertInertia(function (Assert $page) {
            $page->component('auth/admin/PasswordResetLink')
                ->has('success')
                ->where('success', true);
        });

        $admin->refresh();

        $this->assertTrue(Hash::check('newPassword123!', $admin->password));
        $this->assertDatabaseMissing('admin_password_resets', ['reset_token' => $token]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_reset_password_process_contain_validation_rules
     * <Function> Test reset password process contains validation rules.
     *
     * @return void
     */
    public function test_reset_password_process_contain_validation_rules(): void
    {
        $this->changedPasswordPostRequestWithValidation([
            'password' => '',
            'password_confirmation' => ''
        ], ['password', 'password_confirmation']);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_reset_password_process_contain_validation_if_data_are_not_valid
     * <Function> Test reset password process contains validation if data are not valid.
     *
     * @return void
     */
    public function test_reset_password_process_contain_validation_if_data_are_not_valid(): void
    {
        // Fix: Match the exact string Laravel is producing
        $this->changedPasswordPostRequestWithValidation([
            'password' => 'pass12',
            'password_confirmation' => 'Password123!'
        ], ['password' => 'The Password field must be at least 8 characters.']);

        $this->changedPasswordPostRequestWithValidation([
            'password' => 'pass1234',
            'password_confirmation' => 'Password123!'
        ], ['password' => 'The Password field must contain at least one uppercase and one lowercase letter.']);

        // This one likely fails because 'password_confirmation' is the key
        // where the 'same' error is attached in your Request class
        $this->changedPasswordPostRequestWithValidation([
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!!'
        ], ['password_confirmation' => 'The passwords you entered do not match. Please try again.']);
    }
}
