<?php

namespace Tests\Feature\Admin\Corsight;

use App\Models\CorsightDevice;
use App\Models\CorsightDeviceData;
use App\Models\CorsightPersonSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsightPruneTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // makeSession                                                         (1.0) 
    // makeDiaryRow                                                        (2.0) 
    // test_prune_applies_retention                                        (3.0) 
    // test_prune_dry_run_changes_nothing                                  (4.0) 

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
     * <Processing name> test_prune_applies_retention
     * <Function> Check that prune deletes expired diary rows, clears expired
     *            visit photos, deletes expired finished sessions and never
     *            touches in-sessions.
     *
     * @return void
     */
    public function test_prune_applies_retention()
    {
        $device = CorsightDevice::factory()->create();

        $this->makeDiaryRow($device, ['received_at' => now()->subDays(91)]);
        $this->makeDiaryRow($device, ['received_at' => now()->subDays(1)]);

        $expired = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subDays(366),
            'exit_at' => now()->subDays(366),
            'last_seen_at' => now()->subDays(366),
            'image' => 'BASE64-EXPIRED',
        ]);
        $oldWithPhoto = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subDays(91),
            'exit_at' => now()->subDays(91),
            'last_seen_at' => now()->subDays(91),
            'image' => 'BASE64-OLD',
        ]);
        $this->makeSession($device, [
            'status' => 'in',
            'enter_at' => now()->subDays(366),
            'last_seen_at' => now()->subDays(366),
            'image' => 'BASE64-OPEN',
        ]);

        $this->artisan('corsight:prune')->assertExitCode(0);

        $this->assertNull($expired->fresh());
        $this->assertNull($oldWithPhoto->fresh()->image);
        $this->assertSame(2, CorsightPersonSession::count());
        $this->assertSame(1, CorsightDeviceData::count());
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_prune_dry_run_changes_nothing
     * <Function> Check that the --dry-run flag reports without changing
     *            anything.
     *
     * @return void
     */
    public function test_prune_dry_run_changes_nothing()
    {
        $device = CorsightDevice::factory()->create();

        $this->makeDiaryRow($device, ['received_at' => now()->subDays(91)]);
        $expired = $this->makeSession($device, [
            'status' => 'exited',
            'enter_at' => now()->subDays(366),
            'exit_at' => now()->subDays(366),
            'last_seen_at' => now()->subDays(366),
            'image' => 'BASE64-EXPIRED',
        ]);

        $this->artisan('corsight:prune', ['--dry-run' => true])->assertExitCode(0);

        $this->assertNotNull($expired->fresh());
        $this->assertSame('BASE64-EXPIRED', $expired->fresh()->image);
        $this->assertSame(1, CorsightDeviceData::count());
    }
}
