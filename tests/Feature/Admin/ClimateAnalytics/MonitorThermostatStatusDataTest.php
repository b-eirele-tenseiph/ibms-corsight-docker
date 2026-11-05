<?php

namespace Tests\Feature\Admin\ClimateAnalytics;

use App\Events\Lorawan\Climate\ClimateDataUpdated;
use App\Jobs\Lorawan\Thermostat\MonitorThermostatStatusData;
use App\Models\ClimateDataRecord;
use App\Models\LorawanDevice;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MonitorThermostatStatusDataTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy */
    /**************************************************************************/
    // makeThermostatDevice            (1.0) Create a thermostat device in a
    //                                         room.
    // rows                            (2.0) Build decoded uplink rows.
    // test_tracked_types_are_stored   (3.0) One record per tracked type is
    //                                         written.
    // test_readings_bucket_by_hour    (4.0) Readings are stored in the next
    //                                         UTC hour.
    // test_untracked_types_are_ignored (5.0) Unknown types are not stored.
    // test_event_carries_room_identity (6.0) The broadcast payload includes
    //                                       the room and area identity.
    // test_classification_identifies_thermostats (7.0) Only RCF230CD slaves
    //                                         are classified as thermostats.
    // test_non_thermostat_slaves_are_not_thermostats (8.0) Other UC300 slave
    //                                         models are not thermostats.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> makeThermostatDevice
     * <Function> Create a thermostat device bound to a room and area.
     */
    private function makeThermostatDevice(Room $room, string $slaveModel = 'RCF230CD'): LorawanDevice
    {
        return LorawanDevice::factory()->create([
            'model' => 'UC300',
            'context' => ['slave_model' => $slaveModel],
            'room_id' => $room->id,
            'room_area_id' => $room->roomAreas()->first()->id,
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> rows
     * <Function> Build decoded uplink rows.
     *
     * @return Collection<string>
     */
    private function rows(array $types, Carbon $receivedAt): Collection
    {
        return collect($types)->map(fn(string $type) => [
            'type' => $type,
            'context' => ['value' => $type === 'slave_power' ? 'on' : '25.5'],
            'received_at' => $receivedAt,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_tracked_types_are_stored
     * <Function> Check that one record per tracked type is written.
     */
    public function test_tracked_types_are_stored(): void
    {
        $room = Room::factory()->create();
        $device = $this->makeThermostatDevice($room);

        $receivedAt = Carbon::parse('2026-10-21 08:00:30', 'Asia/Manila');

        (new MonitorThermostatStatusData(
            $device,
            $this->rows(['slave_power', 'fan_speed', 'temperature', 'current_setpoint'], $receivedAt)
        ))->handle();

        $records = ClimateDataRecord::where('device_id', $device->id)->get();

        $this->assertCount(4, $records);
        $this->assertSame(
            ['current_setpoint', 'fan_speed', 'slave_power', 'temperature'],
            $records->pluck('type')->sort()->values()->all()
        );

        foreach ($records as $record) {
            $this->assertSame('2026-10-21 01:00:00', $record->date->utc()->format('Y-m-d H:i:s'));
        }
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_readings_bucket_by_hour
     * <Function> Check that readings are stored in the next UTC hour.
     */
    public function test_readings_bucket_by_hour(): void
    {
        $room = Room::factory()->create();
        $device = $this->makeThermostatDevice($room);

        (new MonitorThermostatStatusData(
            $device,
            $this->rows(['temperature'], Carbon::parse('2026-10-21 08:15:00', 'Asia/Manila'))
        ))->handle();

        $this->assertSame(
            '2026-10-21 01:00:00',
            ClimateDataRecord::where('device_id', $device->id)->first()->date->utc()->format('Y-m-d H:i:s')
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_untracked_types_are_ignored
     * <Function> Check that unknown types are not stored.
     */
    public function test_untracked_types_are_ignored(): void
    {
        $room = Room::factory()->create();
        $device = $this->makeThermostatDevice($room);

        (new MonitorThermostatStatusData(
            $device,
            $this->rows(['battery_level'], Carbon::parse('2026-10-21 08:00:30', 'Asia/Manila'))
        ))->handle();

        $this->assertSame(0, ClimateDataRecord::count());
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_event_carries_room_identity
     * <Function> Check that a finalized next-hour record broadcasts and that
     *            the payload identifies the room and area it belongs to.
     */
    public function test_event_carries_room_identity(): void
    {
        Event::fake([ClimateDataUpdated::class]);

        $room = Room::factory()->create();
        $device = $this->makeThermostatDevice($room);

        (new MonitorThermostatStatusData(
            $device,
            $this->rows(['temperature'], Carbon::parse('2026-10-21 08:00:30', 'Asia/Manila'))
        ))->handle();

        Event::assertDispatchedTimes(ClimateDataUpdated::class, 1);

        Event::assertDispatched(ClimateDataUpdated::class, function (ClimateDataUpdated $event) use ($device) {
            $payload = $event->broadcastWith();

            return $payload['room_id'] === $device->room_id
                && $payload['room_area_id'] === $device->room_area_id
                && $payload['labels'] === '2026-10-21 09:00'
                && $payload['value'] === '25.5';
        });
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_classification_identifies_thermostats
     * <Function> Check that a RCF230CD slave is classified as a thermostat.
     */
    public function test_classification_identifies_thermostats(): void
    {
        $room = Room::factory()->create();
        $device = $this->makeThermostatDevice($room);

        $this->assertSame('thermostat', $device->classification);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_non_thermostat_slaves_are_not_thermostats
     * <Function> Check that other UC300 slave models are not thermostats.
     */
    public function test_non_thermostat_slaves_are_not_thermostats(): void
    {
        $room = Room::factory()->create();
        $device = $this->makeThermostatDevice($room, 'EPM5500P');

        $this->assertNotSame('thermostat', $device->classification);
    }
}
