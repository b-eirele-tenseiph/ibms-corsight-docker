<?php

namespace Tests\Feature\Admin\Corsight;

use App\Models\CorsightDevice;
use App\Models\CorsightDeviceData;
use App\Models\CorsightPersonSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\Data;

class CorsightPersonControllerTest extends TestCase
{
    use Data, RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createCorsightAdmin                                                 (1.0) 
    // makeSession                                                         (2.0) 
    // makeAppearanceRow                                                   (3.0) 
    // test_admin_can_view_corsight_person                                 (4.0) 
    // test_admin_can_search_corsight_person                               (5.0) 
    // test_admin_can_view_corsight_person_detail                          (6.0) 
    // test_admin_without_permission_cannot_access                         (7.0) 

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createCorsightAdmin
     * <Function> Creation of admin account with the manage corsight
     *            permission.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createCorsightAdmin()
    {
        $permission = Permission::firstOrCreate([
            'name' => 'manage corsight',
            'guard_name' => 'admin',
        ]);
        $admin = $this->createAdminUser();
        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> makeSession
     * <Function> Create a person session with the given attributes.
     *
     * @param  \App\Models\CorsightDevice  $device
     * @param  array  $overrides
     * @return \App\Models\CorsightPersonSession
     */
    private function makeSession(CorsightDevice $device, array $overrides = []): CorsightPersonSession
    {
        static $sequence = 0;
        $sequence++;

        return CorsightPersonSession::create(array_replace([
            'device_id' => $device->id,
            'appearance_id' => "appearance-{$sequence}",
            'camera_id' => 'camera-123',
            'status' => 'exited',
            'enter_at' => now()->subMinutes(10),
            'exit_at' => now()->subMinutes(5),
            'dwell_seconds' => 300,
            'last_seen_at' => now()->subMinutes(5),
            'event_count' => 2,
        ], $overrides));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> makeAppearanceRow
     * <Function> Create an appearance diary row linked to a session
     *            (matched by appearance_data.appearance_id).
     *
     * @param  \App\Models\CorsightDevice  $device
     * @param  \App\Models\CorsightPersonSession  $session
     * @param  int  $trigger
     * @return \App\Models\CorsightDeviceData
     */
    private function makeAppearanceRow(CorsightDevice $device, CorsightPersonSession $session, int $trigger = 0): CorsightDeviceData
    {
        return CorsightDeviceData::create([
            'event_id' => (string) Str::uuid(),
            'camera_id' => $session->camera_id,
            'device_id' => $device->id,
            'event_type' => 'appearance',
            'context' => [
                'event_type' => 'appearance',
                'trigger' => $trigger,
                'camera_data' => [
                    'camera_id' => $session->camera_id,
                    'camera_description' => $device->name,
                ],
                'appearance_data' => [
                    'appearance_id' => $session->appearance_id,
                    'utc_time_started' => 1778239200.0,
                ],
            ],
            'received_at' => $session->enter_at,
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_admin_can_view_corsight_person
     * <Function> Check if the admin can view the corsight person page with
     *            sessions, counters, the device filter list and filters.
     *
     * @return void
     */
    public function test_admin_can_view_corsight_person(): void
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $device = CorsightDevice::factory()->create();
        $this->makeSession($device);

        $response = $this->get(route('admin.management.corsight.people.index'))->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) {
            $page->component('admin/corsight-person/Index')
                ->has('people.data', 1)
                ->where('people.data.0.status', 'exited')
                ->has('counters.in_now')
                ->has('counters.security')
                ->has('counters.entries')
                ->has('counters.exits')
                ->has('devices')
                ->has('filters');
        });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_admin_can_search_corsight_person
     * <Function> Check if the admin can filter sessions by device search.
     *
     * @return void
     */
    public function test_admin_can_search_corsight_person()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $lobby = CorsightDevice::factory()->create(['name' => 'Lobby Camera']);
        $stairwell = CorsightDevice::factory()->create(['name' => 'Stairwell Camera']);

        $this->makeSession($lobby);
        $this->makeSession($stairwell);

        $response = $this->get(route('admin.management.corsight.people.index', ['search' => 'Lobby']))->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) {
            $page->component('admin/corsight-person/Index')
                ->has('people.data', 1)
                ->where('people.data.0.camera', 'Lobby Camera');
        });
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_admin_can_view_corsight_person_detail
     * <Function> Check that the detail endpoint returns a single session
     *            with its appearance trail.
     *
     * @return void
     */
    public function test_admin_can_view_corsight_person_detail()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $device = CorsightDevice::factory()->create(['name' => 'Lobby Camera']);
        $session = $this->makeSession($device);

        $this->makeAppearanceRow($device, $session, 0);
        $this->makeAppearanceRow($device, $session, 2);

        $response = $this->getJson(route('admin.management.corsight.people.show', $session))->assertStatus(200);

        $response->assertJson([
            'id' => $session->id,
            'appearance_id' => $session->appearance_id,
            'status' => 'exited',
            'camera' => 'Lobby Camera',
            'security_flag' => false,
        ]);
        $response->assertJsonCount(2, 'trail');
        $response->assertJsonPath('trail.0.trigger', 0);
        $response->assertJsonPath('trail.1.trigger', 2);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_admin_without_permission_cannot_access
     * <Function> Check that an admin without the manage corsight permission
     *            is rejected.
     *
     * @return void
     */
    public function test_admin_without_permission_cannot_access()
    {
        $this->actingAs($this->createAdminUser(), 'admin');

        $this->get(route('admin.management.corsight.people.index'))->assertStatus(403);
    }
}
