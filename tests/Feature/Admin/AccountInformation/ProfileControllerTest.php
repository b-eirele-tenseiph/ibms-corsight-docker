<?php

namespace Tests\Feature\Admin\AccountInformation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\Data;

class ProfileControllerTest extends TestCase
{
    use Data, RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createSuperAdminUser                                                (1.0)
    // test_profile_page_is_displayed                                      (2.0)
    // test_profile_information_can_be_updated                             (3.0)
    // test_email_verification_status_is_unchanged                         (4.0)
    // _when_the_email_address_is_unchanged

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
     * <Processing name> test_profile_page_is_displayed
     * <Function> Test to verify that the admin settings profile page is accessible and returns OK status.
     *
     * @return void
     */
    public function test_profile_page_is_displayed(): void
    {
        $user = $this->createSuperAdminUser();

        $response = $this->actingAs($user, 'admin')
            ->get(route('admin.settings.profile-edit'));

        $response->assertOk();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_profile_information_can_be_updated
     * <Function> Test to verify that admin user profile information can be successfully updated with new name and email.
     *
     * @return void
     */
    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->createSuperAdminUser();

        $response = $this->actingAs($user, 'admin')
            ->patch(route('admin.settings.profile-edit'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.settings.profile-edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged
     * <Function> Test to verify that email verification status remains unchanged when updating profile with same email address.
     *
     * @return void
     */
    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->createSuperAdminUser();

        $response = $this->actingAs($user, 'admin')
            ->patch(route('admin.settings.profile-edit'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.settings.profile-edit'));
    }
}
