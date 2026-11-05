<?php

namespace Tests\Feature\Admin\AccountInformation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\Data;

class PasswordControllerTest extends TestCase
{
    use Data, RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createSuperAdminUser                                                (1.0)
    // test_password_can_be_updated                                        (2.0)
    // test_correct_password_must_be_provided_to_update_password           (3.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createSuperAdminUser
     * <Function> Creation of admin account with super admin role.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createSuperAdminUser()
    {
        return $this->createAdminUser();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_password_can_be_updated
     * <Function> Test to verify that user can successfully update their password with correct current password.
     *
     * @return void
     */
    public function test_password_can_be_updated(): void
    {
        $user = $this->createSuperAdminUser();

        $response = $this->actingAs($user, 'admin')
            ->from(route('admin.settings.password-edit'))
            ->put(route('admin.settings.password-edit'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.settings.password-edit'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_correct_password_must_be_provided_to_update_password
     * <Function> Test to verify that password update fails when incorrect current password is provided.
     *
     * @return void
     */
    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = $this->createSuperAdminUser();

        $response = $this->actingAs($user, 'admin')
            ->from(route('admin.settings.password-edit'))
            ->put(route('admin.settings.password-edit'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('admin.settings.password-edit'));
    }
}
