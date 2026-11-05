<?php

namespace Tests\Feature\Admin\ClimateAnalytics;

use App\Models\Admin;
use App\Models\ClimateDataRecord;
use App\Models\LorawanDevice;
use App\Models\LorawanDeviceData;
use App\Models\Room;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ClimateDataControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy */
    /**************************************************************************/
    // setUp                                (1.0) Seed roles and permissions.
    // makeThermostatDevice                 (2.0) Create a thermostat device in
    //                                            a room.
    // test_admin_can_view_index            (3.0) The index page renders with
    //                                            rooms and their areas.
    // test_endpoints_return_labels         (4.0) Empty chart data returns
    //                                            hourly labels.
    // test_endpoints_return_records        (5.0) Recorded values appear in
    //                                            datasets.
    // test_custom_range_returns_daily      (6.0) A long custom range is
    //                                            bucketed per day.
    // test_other_room_records_excluded     (7.0) Records from other rooms are
    //                                            excluded.
    // test_staff_cannot_view_index         (8.0) Staff without the permission
    //                                            are denied the index page.
    // test_staff_cannot_access_chart       (9.0) Staff without the permission
    //                                            endpoints are denied all chart
    //                                            endpoints.
    // test_super_admin_can_view_index      (10.0) Super admin can view the
    //                                            index page.
    // test_setpoint_keeps_latest_value     (11.0) Setpoint buckets keep the
    //                                            latest reading, not an average.
    // test_utc_records_render_in_manila    (12.0) UTC records are labelled in
    //                                            Asia/Manila for the frontend.
    // test_readings_group_by_utc_minute    (13.0) Raw readings from one uplink
    //                                            are grouped and returned.
    // test_readings_keep_auto_fan          (14.0) Raw readings preserve Auto
    //                                            while the chart maps it to 4.
    // test_readings_fall_back_to_chart     (15.0) Old ranges without raw
    //                                            uplinks use chart records.
    // test_readings_prefer_raw_over_chart  (16.0) Raw uplinks win over the
    //                                            chart fallback in one hour.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> setUp
     * <Function> Seed roles and permissions.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> makeThermostatDevice
     * <Function> Create a thermostat device bound to a room and area.
     */
    private function makeThermostatDevice(Room $room): LorawanDevice
    {
        return LorawanDevice::factory()->create([
            'model' => 'UC300',
            'context' => ['slave_model' => 'RCF230CD'],
            'room_id' => $room->id,
            'room_area_id' => $room->roomAreas()->first()->id,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_admin_can_view_index
     * <Function> Check that an admin can view the climate analytics page.
     */
    public function test_admin_can_view_index(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $this->makeThermostatDevice($room);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.climate-analytics.index'));

        $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/climate-analytics/Index')
            ->has('rooms', 1)
            ->has('rooms.0.room_areas')
            ->has('filters.resolution'));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_endpoints_return_labels
     * <Function> Check that chart endpoints return hourly labels with no datasets.
     */
    public function test_endpoints_return_labels(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $this->makeThermostatDevice($room);

        $routes = [
            'admin.climate-analytics.actual-and-setpoint',
            'admin.climate-analytics.fan.speed',
            'admin.climate-analytics.power.status',
            'admin.climate-analytics.temperature',
            'admin.climate-analytics.setpoint',
        ];

        $today = Carbon::now('Asia/Manila')->toDateString();
        $expectedLabels = Carbon::now('Asia/Manila')->hour + 1;

        foreach ($routes as $route) {
            $this->actingAs($admin, 'admin')
                ->getJson(route($route, [
                    'start' => $today,
                    'end' => $today,
                    'room' => $room->id,
                    'room_area' => $area->id,
                ]))
                ->assertOk()
                ->assertJsonCount($expectedLabels, 'labels')
                ->assertJsonCount(0, 'datasets');
        }
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_endpoints_return_records
     * <Function> Check that recorded values appear in the chart datasets.
     */
    public function test_endpoints_return_records(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $device = $this->makeThermostatDevice($room);

        // A past date always produces the full 24 hourly labels. Records are
        // stored in UTC: 00:00/01:00 UTC render as 08:00/09:00 Manila.
        $date = Carbon::now('Asia/Manila')->subDay()->toDateString();

        ClimateDataRecord::create(['device_id' => $device->id, 'date' => $date.' 00:00:00', 'type' => 'temperature', 'value' => '25.5']);
        ClimateDataRecord::create(['device_id' => $device->id, 'date' => $date.' 01:00:00', 'type' => 'temperature', 'value' => '26']);
        ClimateDataRecord::create(['device_id' => $device->id, 'date' => $date.' 00:00:00', 'type' => 'fan_speed', 'value' => '2']);
        ClimateDataRecord::create(['device_id' => $device->id, 'date' => $date.' 00:00:00', 'type' => 'slave_power', 'value' => 'on']);
        ClimateDataRecord::create(['device_id' => $device->id, 'date' => $date.' 00:00:00', 'type' => 'current_setpoint', 'value' => '24']);

        $params = ['start' => $date, 'end' => $date, 'room' => $room->id, 'room_area' => $area->id];
        $expected = fn (array $values) => array_merge(array_fill(0, 8, null), $values, array_fill(0, 24 - count($values) - 8, null));

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.temperature', $params))
            ->assertJson(['datasets' => [['data' => $expected([25.5, 26])]]]);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.fan.speed', $params))
            ->assertJson(['datasets' => [['data' => $expected([2])]]]);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.power.status', $params))
            ->assertJson(['datasets' => [['data' => $expected([1])]]]);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.setpoint', $params))
            ->assertJson(['datasets' => [['data' => $expected([24])]]]);

        $merged = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.actual-and-setpoint', $params))
            ->assertJsonCount(2, 'datasets')
            ->json('datasets');

        $this->assertEquals(24, $merged[0]['data'][8]);
        $this->assertEquals(25.5, $merged[1]['data'][8]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_custom_range_returns_daily
     * <Function> Check that a window longer than a week is bucketed per day.
     */
    public function test_custom_range_returns_daily(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $this->makeThermostatDevice($room);

        $end = Carbon::now('Asia/Manila')->subDay()->startOfDay();
        $start = $end->copy()->subDays(29);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.temperature', [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'room' => $room->id,
                'room_area' => $area->id,
            ]))
            ->assertOk();

        $this->assertCount(30, $response->json('labels'));
        $this->assertStringStartsWith($start->toDateString(), $response->json('labels.0'));
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_other_room_records_excluded
     * <Function> Check that records from other rooms are excluded by the room filter.
     */
    public function test_other_room_records_excluded(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $otherRoom = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $this->makeThermostatDevice($room);

        $otherDevice = LorawanDevice::factory()->create([
            'model' => 'UC300',
            'context' => ['slave_model' => 'RCF230CD'],
            'room_id' => $otherRoom->id,
            'room_area_id' => $otherRoom->roomAreas()->first()->id,
        ]);

        $today = Carbon::now('Asia/Manila')->toDateString();
        ClimateDataRecord::create(['device_id' => $otherDevice->id, 'date' => $today.' 00:00:00', 'type' => 'temperature', 'value' => '30']);

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.temperature', [
                'start' => $today,
                'end' => $today,
                'room' => $room->id,
                'room_area' => $area->id,
            ]))
            ->assertJsonCount(0, 'datasets');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_staff_cannot_view_index
     * <Function> Check that a staff admin without the permission is denied
     *            the index page.
     */
    public function test_staff_cannot_view_index(): void
    {
        $staff = Admin::factory()->create(['enabled' => true])->assignRole('staff');

        $this->actingAs($staff, 'admin')
            ->get(route('admin.climate-analytics.index'))
            ->assertForbidden();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_staff_cannot_access_chart_endpoints
     * <Function> Check that a staff admin without the permission is denied
     *            all chart endpoints.
     */
    public function test_staff_cannot_access_chart_endpoints(): void
    {
        $staff = Admin::factory()->create(['enabled' => true])->assignRole('staff');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();

        $routes = [
            'admin.climate-analytics.actual-and-setpoint',
            'admin.climate-analytics.fan.speed',
            'admin.climate-analytics.power.status',
            'admin.climate-analytics.temperature',
            'admin.climate-analytics.setpoint',
            'admin.climate-analytics.readings',
        ];

        foreach ($routes as $route) {
            $this->actingAs($staff, 'admin')
                ->getJson(route($route, [
                    'start' => Carbon::now('Asia/Manila')->toDateString(),
                    'end' => Carbon::now('Asia/Manila')->toDateString(),
                    'room' => $room->id,
                    'room_area' => $area->id,
                ]))
                ->assertForbidden();
        }
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> test_setpoint_keeps_latest_value
     * <Function> Check that a setpoint bucket reports its latest reading
     *            instead of an average of the values inside it.
     */
    public function test_setpoint_keeps_latest_value(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $device = $this->makeThermostatDevice($room);

        $start = Carbon::now('Asia/Manila')->subDays(4)->startOfDay();
        $end = $start->copy()->addDay();

        ClimateDataRecord::create(['device_id' => $device->id, 'date' => $start->copy()->setTime(6, 0)->utc()->format('Y-m-d H:i:s'), 'type' => 'current_setpoint', 'value' => '25']);
        ClimateDataRecord::create(['device_id' => $device->id, 'date' => $start->copy()->setTime(8, 0)->utc()->format('Y-m-d H:i:s'), 'type' => 'current_setpoint', 'value' => '24']);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.setpoint', [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'room' => $room->id,
                'room_area' => $area->id,
            ]))
            ->assertOk();

        $index = array_search($start->format('Y-m-d').' 06:00', $response->json('labels'), true);

        $this->assertNotFalse($index);
        $this->assertEquals(24, $response->json("datasets.0.data.{$index}"));
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> test_utc_records_render_in_manila
     * <Function> Check that records stored in UTC are labelled in Asia/Manila
     *            for the frontend.
     */
    public function test_utc_records_render_in_manila(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $device = $this->makeThermostatDevice($room);

        // 00:00 UTC is 08:00 in Asia/Manila.
        ClimateDataRecord::create([
            'device_id' => $device->id,
            'date' => '2026-09-15 00:00:00',
            'type' => 'temperature',
            'value' => '25.5',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.temperature', [
                'start' => '2026-09-15',
                'end' => '2026-09-15',
                'room' => $room->id,
                'room_area' => $area->id,
            ]))
            ->assertOk();

        $response->assertJsonPath('labels.8', '2026-09-15 08:00');
        $this->assertEquals(25.5, $response->json('datasets.0.data.8'));
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> test_readings_group_by_utc_minute
     * <Function> Check that raw uplink rows in the same UTC minute become one
     *            reading with the original timestamp and fan value.
     */
    public function test_readings_group_by_utc_minute(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $device = $this->makeThermostatDevice($room);

        $receivedAt = '2026-09-15 00:00:10';

        foreach ([
            ['temperature', '25.5'],
            ['temperature', '26.5'],
            ['current_setpoint', '24'],
            ['slave_power', 'on'],
            ['fan_speed', '2'],
        ] as [$type, $value]) {
            LorawanDeviceData::create([
                'device_id' => $device->id,
                'type' => $type,
                'context' => ['value' => $value],
                'transmission' => LorawanDeviceData::TRANSMISSION_UPLINK,
                'received_at' => $receivedAt,
            ]);
        }

        // Downlink commands must not leak into the uplink readings.
        LorawanDeviceData::create([
            'device_id' => $device->id,
            'type' => 'temperature',
            'context' => ['value' => '99'],
            'transmission' => LorawanDeviceData::TRANSMISSION_DOWNLINK,
            'received_at' => $receivedAt,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.readings', [
                'start' => '2026-09-15',
                'end' => '2026-09-15',
                'room' => $room->id,
                'room_area' => $area->id,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'readings');

        $reading = $response->json('readings.0');

        $this->assertSame('2026-09-15T00:00:10+00:00', $reading['timestamp']);
        $this->assertEquals(26, $reading['temperature']);
        $this->assertEquals(24, $reading['setpoint']);
        $this->assertSame('on', $reading['power']);
        $this->assertSame(2, $reading['fan_speed']);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> test_readings_keep_auto_fan
     * <Function> Check that Auto stays Auto in raw readings while the fan chart
     *            keeps it visible as level 4 instead of casting it to Off.
     */
    public function test_readings_keep_auto_fan(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $device = $this->makeThermostatDevice($room);

        LorawanDeviceData::create([
            'device_id' => $device->id,
            'type' => 'fan_speed',
            'context' => ['value' => 'auto'],
            'transmission' => LorawanDeviceData::TRANSMISSION_UPLINK,
            'received_at' => '2026-09-15 00:00:10',
        ]);

        ClimateDataRecord::create([
            'device_id' => $device->id,
            'date' => '2026-09-15 00:00:00',
            'type' => 'fan_speed',
            'value' => 'auto',
        ]);

        $params = [
            'start' => '2026-09-15',
            'end' => '2026-09-15',
            'room' => $room->id,
            'room_area' => $area->id,
        ];

        $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.readings', $params))
            ->assertOk()
            ->assertJsonPath('readings.0.fan_speed', 'auto');

        $chart = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.fan.speed', $params))
            ->assertOk();

        $index = array_search('2026-09-15 08:00', $chart->json('labels'), true);

        $this->assertNotFalse($index);
        $this->assertSame(4, $chart->json("datasets.0.data.{$index}"));
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> test_readings_fall_back_to_chart_records
     * <Function> Check that an old range without raw uplinks still returns
     *            readings from the hourly chart records.
     */
    public function test_readings_fall_back_to_chart_records(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $device = $this->makeThermostatDevice($room);

        foreach ([
            ['temperature', '25.5'],
            ['current_setpoint', '24'],
            ['slave_power', 'on'],
            ['fan_speed', '2'],
        ] as [$type, $value]) {
            ClimateDataRecord::create([
                'device_id' => $device->id,
                'date' => '2026-10-01 00:00:00',
                'type' => $type,
                'value' => $value,
            ]);
        }

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.readings', [
                'start' => '2026-10-01',
                'end' => '2026-10-01',
                'room' => $room->id,
                'room_area' => $area->id,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'readings');

        $reading = $response->json('readings.0');

        $this->assertSame('2026-10-01T00:00:00+00:00', $reading['timestamp']);
        $this->assertEquals(25.5, $reading['temperature']);
        $this->assertEquals(24, $reading['setpoint']);
        $this->assertSame('on', $reading['power']);
        $this->assertSame(2, $reading['fan_speed']);
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> test_readings_prefer_raw_over_chart
     * <Function> Check that a raw uplink hour is not duplicated by the hourly
     *            chart fallback.
     */
    public function test_readings_prefer_raw_over_chart(): void
    {
        $admin = Admin::factory()->create(['enabled' => true])->assignRole('admin');
        $room = Room::factory()->create();
        $area = $room->roomAreas()->first();
        $device = $this->makeThermostatDevice($room);

        foreach ([
            ['temperature', '25.5'],
            ['current_setpoint', '24'],
            ['slave_power', 'on'],
            ['fan_speed', '2'],
        ] as [$type, $value]) {
            ClimateDataRecord::create([
                'device_id' => $device->id,
                'date' => '2026-10-01 00:00:00',
                'type' => $type,
                'value' => $value,
            ]);
        }

        foreach ([
            ['temperature', '27.5'],
            ['current_setpoint', '22'],
            ['slave_power', 'off'],
            ['fan_speed', '1'],
        ] as [$type, $value]) {
            LorawanDeviceData::create([
                'device_id' => $device->id,
                'type' => $type,
                'context' => ['value' => $value],
                'transmission' => LorawanDeviceData::TRANSMISSION_UPLINK,
                'received_at' => '2026-10-01 00:05:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.climate-analytics.readings', [
                'start' => '2026-10-01',
                'end' => '2026-10-01',
                'room' => $room->id,
                'room_area' => $area->id,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'readings');

        $reading = $response->json('readings.0');

        $this->assertSame('2026-10-01T00:05:00+00:00', $reading['timestamp']);
        $this->assertEquals(27.5, $reading['temperature']);
        $this->assertEquals(22, $reading['setpoint']);
        $this->assertSame('off', $reading['power']);
        $this->assertSame(1, $reading['fan_speed']);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_super_admin_can_view_index
     * <Function> Check that a super admin can view the index page.
     */
    public function test_super_admin_can_view_index(): void
    {
        $superAdmin = Admin::factory()->create(['enabled' => true])->assignRole('super admin');
        $room = Room::factory()->create();
        $this->makeThermostatDevice($room);

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.climate-analytics.index'))
            ->assertOk();
    }
}
