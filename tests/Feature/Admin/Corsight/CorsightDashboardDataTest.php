<?php

namespace Tests\Feature\Admin\Corsight;

use App\Models\CorsightDevice;
use App\Models\CorsightDeviceData;
use App\Models\CorsightPersonSession;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\Data;
use Tests\TestCase;

class CorsightDashboardDataTest extends TestCase
{
    use Data, RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // makeSession                        (1.0) Create a person session with
    //                                          the given attributes.
    // makeDiaryRow                       (2.0) Create a diary row for a device.
    // test_returns_corsight_dashboard_payload_shape (3.0)
    // test_excludes_sessions_outside_30_day_window (4.0)
    // test_caps_recent_logs_at_500_rows  (5.0)
    // test_returns_images_only_for_newest_thirty_sessions (6.0)
    // test_maps_gender_values            (7.0)
    // test_maps_age_groups_to_dashboard_ranges (8.0)
    // test_direction_reflects_session_status (9.0)
    // test_groups_rooms_by_camera_location_and_counts (10.0)
    // test_computes_dwell_time_per_room  (11.0)
    // test_requires_admin_authentication (12.0)

    /**
     * <Layer number> (1.0)
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
            'status' => 'in',
            'enter_at' => now(),
            'last_seen_at' => now(),
            'event_count' => 1,
        ], $overrides));
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> makeDiaryRow
     * <Function> Create a diary row for a device.
     *
     * @param  \App\Models\CorsightDevice  $device
     * @param  array  $overrides
     * @return \App\Models\CorsightDeviceData
     */
    private function makeDiaryRow(CorsightDevice $device, array $overrides = []): CorsightDeviceData
    {
        return CorsightDeviceData::create(array_replace([
            'event_id' => (string) \Illuminate\Support\Str::uuid(),
            'camera_id' => 'camera-123',
            'device_id' => $device->id,
            'event_type' => 'camera_status',
            'context' => ['update_data' => ['status' => 1]],
            'received_at' => now(),
        ], $overrides));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_returns_corsight_dashboard_payload_shape
     * <Function> Check that the endpoint returns the CCTV dashboard payload
     *            shape with the room label and prefixed face crop.
     *
     * @return void
     */
    public function test_returns_corsight_dashboard_payload_shape()
    {
        $admin = $this->createAdminUser();
        $room = Room::factory()->create(['name' => 'Lobby']);
        $device = CorsightDevice::factory()->create(['room_id' => $room->id]);
        $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subMinutes(10),
            'exit_at' => now()->subMinutes(5),
            'last_seen_at' => now()->subMinutes(5),
            'dwell_seconds' => 300,
            'gender' => 'male',
            'age_group' => '30-34',
            'image' => 'ABC123',
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonStructure([
                'last_updated',
                'recent_logs' => [[
                    'id', 'image', 'gender', 'age_range', 'direction', 'room', 'timestamp',
                ]],
                'rooms' => [[
                    'name', 'active_cameras', 'total_cameras', 'occupancy',
                ]],
                'dwell_time' => [[
                    'room', 'average_dwell_seconds', 'start', 'end', 'duration_seconds',
                ]],
            ]);

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonPath('recent_logs.0.image', 'data:image/jpeg;base64,ABC123')
            ->assertJsonPath('recent_logs.0.room', 'Lobby')
            ->assertJsonPath('rooms.0.name', 'Lobby')
            ->assertJsonPath('rooms.0.total_cameras', 1)
            ->assertJsonPath('rooms.0.active_cameras', 0)
            ->assertJsonPath('rooms.0.occupancy', 0);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_excludes_sessions_outside_30_day_window
     * <Function> Check that sessions older than 30 days are excluded while
     *            recent ones are included.
     *
     * @return void
     */
    public function test_excludes_sessions_outside_30_day_window()
    {
        $admin = $this->createAdminUser();
        $device = CorsightDevice::factory()->create();

        $old = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subDays(31),
            'exit_at' => now()->subDays(31),
            'last_seen_at' => now()->subDays(31),
        ]);
        $recent = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subDay(),
            'exit_at' => now()->subDay()->addMinutes(5),
            'last_seen_at' => now()->subDay()->addMinutes(5),
            'dwell_seconds' => 300,
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonPath('recent_logs.0.id', $recent->id)
            ->assertJsonCount(1, 'recent_logs');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_caps_recent_logs_at_500_rows
     * <Function> Check that the recent logs list is capped at 500 rows.
     *
     * @return void
     */
    public function test_caps_recent_logs_at_500_rows()
    {
        $admin = $this->createAdminUser();
        $device = CorsightDevice::factory()->create();

        for ($i = 0; $i < 502; $i++) {
            $this->makeSession($device, [
                'status' => 'exited',
                'enter_at' => now()->subMinutes($i),
                'exit_at' => now()->subMinutes($i)->addMinute(),
                'last_seen_at' => now()->subMinutes($i)->addMinute(),
                'dwell_seconds' => 60,
            ]);
        }

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonCount(500, 'recent_logs');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_returns_images_only_for_newest_thirty_sessions
     * <Function> Check that face crops are returned for the newest 30 rows
     *            only, with the data-URI prefix, and null afterwards.
     *
     * @return void
     */
    public function test_returns_images_only_for_newest_thirty_sessions()
    {
        $admin = $this->createAdminUser();
        $device = CorsightDevice::factory()->create();

        for ($i = 34; $i >= 0; $i--) {
            $this->makeSession($device, [
                'status' => 'exited',
                'enter_at' => now()->subMinutes($i * 2),
                'exit_at' => now()->subMinutes($i * 2)->addMinute(),
                'last_seen_at' => now()->subMinutes($i * 2)->addMinute(),
                'dwell_seconds' => 60,
                'image' => "IMG-{$i}",
            ]);
        }

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonPath('recent_logs.0.image', 'data:image/jpeg;base64,IMG-0')
            ->assertJsonPath('recent_logs.29.image', 'data:image/jpeg;base64,IMG-29')
            ->assertJsonPath('recent_logs.30.image', null)
            ->assertJsonPath('recent_logs.34.image', null);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_maps_gender_values
     * <Function> Check that raw Fortify gender outcomes map to the
     *            dashboard enum (unknown as the fallback).
     *
     * @return void
     */
    public function test_maps_gender_values()
    {
        $admin = $this->createAdminUser();
        $device = CorsightDevice::factory()->create();

        $rawValues = ['male', 'Female', 'not_determined', null];

        foreach ($rawValues as $i => $raw) {
            $this->makeSession($device, [
                'status' => 'exited',
                'enter_at' => now()->subMinutes($i),
                'exit_at' => now()->subMinutes($i)->addMinute(),
                'last_seen_at' => now()->subMinutes($i)->addMinute(),
                'dwell_seconds' => 60,
                'gender' => $raw,
            ]);
        }

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonPath('recent_logs.0.gender', 'male')
            ->assertJsonPath('recent_logs.1.gender', 'female')
            ->assertJsonPath('recent_logs.2.gender', 'unknown')
            ->assertJsonPath('recent_logs.3.gender', 'unknown');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_maps_age_groups_to_dashboard_ranges
     * <Function> Check that raw Fortify age groups re-bucket into the
     *            dashboard 10-year ranges and null when not determined.
     *
     * @return void
     */
    public function test_maps_age_groups_to_dashboard_ranges()
    {
        $admin = $this->createAdminUser();
        $device = CorsightDevice::factory()->create();

        $rawValues = ['20-24', '35-39', '55+', 'not_determined'];

        foreach ($rawValues as $i => $raw) {
            $this->makeSession($device, [
                'status' => 'exited',
                'enter_at' => now()->subMinutes($i),
                'exit_at' => now()->subMinutes($i)->addMinute(),
                'last_seen_at' => now()->subMinutes($i)->addMinute(),
                'dwell_seconds' => 60,
                'age_group' => $raw,
            ]);
        }

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonPath('recent_logs.0.age_range', '20-29')
            ->assertJsonPath('recent_logs.1.age_range', '30-39')
            ->assertJsonPath('recent_logs.2.age_range', '50+')
            ->assertJsonPath('recent_logs.3.age_range', null);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_direction_reflects_session_status
     * <Function> Check that open sessions report "entered" while closed
     *            sessions (exited or abandoned) report "exited".
     *
     * @return void
     */
    public function test_direction_reflects_session_status()
    {
        $admin = $this->createAdminUser();
        $device = CorsightDevice::factory()->create();

        $open = $this->makeSession($device, [
            'status' => 'in',
            'enter_at' => now()->subMinutes(12),
            'last_seen_at' => now()->subMinutes(12),
        ]);
        $abandoned = $this->makeSession($device, [
            'status' => 'abandoned',
            'enter_at' => now()->subMinutes(8),
            'last_seen_at' => now()->subMinutes(8),
        ]);
        $exited = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subMinutes(4),
            'exit_at' => now()->subMinutes(2),
            'last_seen_at' => now()->subMinutes(2),
            'dwell_seconds' => 120,
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonPath('recent_logs.0.id', $exited->id)
            ->assertJsonPath('recent_logs.0.direction', 'exited')
            ->assertJsonPath('recent_logs.1.direction', 'exited')
            ->assertJsonPath('recent_logs.2.direction', 'entered');
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_groups_rooms_by_camera_location_and_counts
     * <Function> Check that rooms group cameras by room name (camera name
     *            when unassigned), count active vs total cameras by diary
     *            freshness and sum open sessions as occupancy.
     *
     * @return void
     */
    public function test_groups_rooms_by_camera_location_and_counts()
    {
        $admin = $this->createAdminUser();
        $lobby = Room::factory()->create(['name' => 'Lobby']);

        $activeDevice = CorsightDevice::factory()->create(['room_id' => $lobby->id]);
        $staleDevice = CorsightDevice::factory()->create(['room_id' => $lobby->id]);
        $unassignedDevice = CorsightDevice::factory()->create(['room_id' => null]);

        $this->makeDiaryRow($activeDevice, ['received_at' => now()->subMinutes(1)]);
        $this->makeDiaryRow($staleDevice, ['received_at' => now()->subHours(2)]);

        $this->makeSession($activeDevice, ['status' => 'in']);
        $this->makeSession($activeDevice, ['status' => 'in']);
        $this->makeSession($staleDevice, [
            'status' => 'exited',
            'enter_at' => now()->subMinutes(30),
            'exit_at' => now()->subMinutes(25),
            'last_seen_at' => now()->subMinutes(25),
            'dwell_seconds' => 300,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->json();

        $byName = collect($response['rooms'])->keyBy('name');

        $this->assertSame(1, $byName['Lobby']['active_cameras']);
        $this->assertSame(2, $byName['Lobby']['total_cameras']);
        $this->assertSame(2, $byName['Lobby']['occupancy']);
        $this->assertSame(0, $byName[$unassignedDevice->name]['active_cameras']);
        $this->assertSame(1, $byName[$unassignedDevice->name]['total_cameras']);
        $this->assertSame(0, $byName[$unassignedDevice->name]['occupancy']);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> test_computes_dwell_time_per_room
     * <Function> Check that the dwell panel averages closed-session dwell
     *            per room and anchors the latest visit's start/end.
     *
     * @return void
     */
    public function test_computes_dwell_time_per_room()
    {
        $admin = $this->createAdminUser();
        $lobby = Room::factory()->create(['name' => 'Lobby']);
        $device = CorsightDevice::factory()->create(['room_id' => $lobby->id]);

        $first = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subHours(3),
            'exit_at' => now()->subHours(3)->addSeconds(1800),
            'last_seen_at' => now()->subHours(3)->addSeconds(1800),
            'dwell_seconds' => 1800,
        ]);
        $latest = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subHour(),
            'exit_at' => now()->subMinutes(55),
            'last_seen_at' => now()->subMinutes(55),
            'dwell_seconds' => 300,
        ]);

        $this->actingAs($admin, 'admin')
            ->getJson('/admin/dashboard/corsight-data')
            ->assertOk()
            ->assertJsonCount(1, 'dwell_time')
            ->assertJsonPath('dwell_time.0.room', 'Lobby')
            ->assertJsonPath('dwell_time.0.average_dwell_seconds', 1050)
            ->assertJsonPath('dwell_time.0.start', $latest->enter_at->toIso8601ZuluString())
            ->assertJsonPath('dwell_time.0.end', $latest->exit_at->toIso8601ZuluString())
            ->assertJsonPath('dwell_time.0.duration_seconds', 300);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> test_requires_admin_authentication
     * <Function> Check that unauthenticated visitors are redirected away
     *            from the endpoint.
     *
     * @return void
     */
    public function test_requires_admin_authentication()
    {
        $this->get('/admin/dashboard/corsight-data')->assertStatus(302);
    }
}
