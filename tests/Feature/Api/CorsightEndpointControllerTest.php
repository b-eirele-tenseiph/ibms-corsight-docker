<?php

namespace Tests\Feature\Api;

use App\Models\CorsightAccount;
use App\Models\CorsightDevice;
use App\Models\CorsightPersonSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsightEndpointControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // accountWith                                                         (1.0) 
    // eventBody                                                           (2.0) 
    // sign                                                                (3.0) 
    // postSigned                                                          (4.0)
    // test_no_account_configured_is_rejected                              (5.0) 
    // test_signed_event_is_stored                                         (6.0) 
    // test_unknown_camera_is_not_stored                                   (7.0) 
    // test_registered_camera_stores_device_id                             (8.0) 
    // test_invalid_signature_is_rejected                                  (9.0) 
    // test_missing_signature_is_rejected                                 (10.0) 
    // test_duplicate_event_id_is_stored_once                             (11.0) 
    // test_second_account_secret_also_verifies                           (12.0) 
    // test_storage_disabled_device_is_not_stored                         (13.0) 
    // test_event_without_camera_label_is_dropped                         (14.0) 
    // appearanceBody                                                     (15.0) 
    // test_appearance_lifecycle_creates_and_closes_session               (16.0) 
    // test_camera_status_display_mode_abandons_open_sessions             (17.0) 
    // test_duplicate_event_does_not_mutate_session_twice                 (18.0) 

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> accountWith
     * <Function> Create an account holding a known webhook secret.
     *
     * @param  string  $secret
     * @return \App\Models\CorsightAccount
     */
    private function accountWith(string $secret): CorsightAccount
    {
        return CorsightAccount::factory()->create([
            'username' => 'test-user',
            'endpoint_secret' => $secret,
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> eventBody
     * <Function> Build a raw Fortify event envelope body.
     *
     * @param  string  $eventId
     * @param  string  $type
     * @param  string  $cameraId
     * @param  string|null  $cameraDescription
     * @return string
     */
    private function eventBody(string $eventId, string $type = 'appearance', string $cameraId = 'camera-123', ?string $cameraDescription = null): string
    {
        $cameraData = ['camera_id' => $cameraId];

        if ($cameraDescription !== null) {
            $cameraData['camera_description'] = $cameraDescription;
        }

        return json_encode([
            'msg' => 'test-event',
            'event_type' => $type,
            'event_id' => $eventId,
            'msg_send_timestamp' => 1778239212.067,
            'camera_data' => $cameraData,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> sign
     * <Function> Sign a raw body with a given secret.
     *
     * @param  string  $body
     * @param  string  $secret
     * @return string
     */
    private function sign(string $body, string $secret): string
    {
        return hash_hmac('sha256', $body, $secret);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> postSigned
     * <Function> POST a signed raw body to the endpoint and return the
     *            response.
     *
     * @param  string  $body
     * @param  string|null  $signature
     * @return \Illuminate\Testing\TestResponse
     */
    private function postSigned(string $body, ?string $signature = null)
    {
        $server = ['CONTENT_TYPE' => 'application/json'];

        if ($signature !== null) {
            $server['HTTP_X_FORTIFY_SIGNATURE'] = $signature;
        }

        return $this->call('POST', route('corsight-endpoint'), server: $server, content: $body);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_no_account_configured_is_rejected
     * <Function> Check that with no account rows the endpoint rejects.
     *
     * @return void
     */
    public function test_no_account_configured_is_rejected()
    {
        $body = $this->eventBody('event-0');

        $response = $this->postSigned($body, $this->sign($body, 'any-secret'));

        $response->assertStatus(401);
        $this->assertDatabaseCount('corsight_device_data', 0);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_signed_event_is_stored
     * <Function> Check if a correctly signed event is stored and acknowledged.
     *
     * @return void
     */
    public function test_signed_event_is_stored()
    {
        $this->accountWith('test-webhook-secret');
        $device = CorsightDevice::factory()->create(['name' => 'Conference Room']);
        $body = $this->eventBody('event-1', 'appearance', 'camera-123', 'Conference Room');

        $response = $this->postSigned($body, $this->sign($body, 'test-webhook-secret'));

        $response->assertStatus(204);

        $this->assertDatabaseCount('corsight_device_data', 1);
        $this->assertDatabaseHas('corsight_device_data', [
            'event_id' => 'event-1',
            'event_type' => 'appearance',
            'camera_id' => 'camera-123',
            'device_id' => $device->id,
        ]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_unknown_camera_is_not_stored
     * <Function> Check that a camera label matching no device is ack'd
     *            but nothing is stored.
     *
     * @return void
     */
    public function test_unknown_camera_is_not_stored()
    {
        $this->accountWith('test-webhook-secret');
        $body = $this->eventBody('event-2', 'appearance', 'unregistered-camera', 'Unregistered Room');

        $response = $this->postSigned($body, $this->sign($body, 'test-webhook-secret'));

        $response->assertStatus(204);
        $this->assertDatabaseCount('corsight_device_data', 0);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_registered_camera_stores_device_id
     * <Function> Check that a registered camera stores with its device_id.
     *
     * @return void
     */
    public function test_registered_camera_stores_device_id()
    {
        $this->accountWith('test-webhook-secret');
        $device = CorsightDevice::factory()->create(['name' => 'Conference Room']);

        $body = $this->eventBody('event-3', 'appearance', 'camera-3', 'Conference Room');

        $response = $this->postSigned($body, $this->sign($body, 'test-webhook-secret'));

        $response->assertStatus(204);
        $this->assertDatabaseHas('corsight_device_data', [
            'event_id' => 'event-3',
            'device_id' => $device->id,
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_invalid_signature_is_rejected
     * <Function> Check if a wrong signature is rejected and nothing is stored.
     *
     * @return void
     */
    public function test_invalid_signature_is_rejected()
    {
        $this->accountWith('test-webhook-secret');
        $body = $this->eventBody('event-4');

        $response = $this->postSigned($body, $this->sign($body, 'wrong-secret'));

        $response->assertStatus(401);
        $this->assertDatabaseCount('corsight_device_data', 0);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_missing_signature_is_rejected
     * <Function> Check if a missing signature header is rejected.
     *
     * @return void
     */
    public function test_missing_signature_is_rejected()
    {
        $this->accountWith('test-webhook-secret');
        $body = $this->eventBody('event-5');

        $response = $this->postSigned($body);

        $response->assertStatus(401);
        $this->assertDatabaseCount('corsight_device_data', 0);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> test_duplicate_event_id_is_stored_once
     * <Function> Check if a replayed event_id is not stored twice.
     *
     * @return void
     */
    public function test_duplicate_event_id_is_stored_once()
    {
        $this->accountWith('test-webhook-secret');
        CorsightDevice::factory()->create(['name' => 'Conference Room']);
        $body = $this->eventBody('event-6', 'appearance', 'camera-123', 'Conference Room');
        $signature = $this->sign($body, 'test-webhook-secret');

        $this->postSigned($body, $signature)->assertStatus(204);
        $this->postSigned($body, $signature)->assertStatus(204);

        $this->assertDatabaseCount('corsight_device_data', 1);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> test_second_account_secret_also_verifies
     * <Function> Check that the multi-account loop accepts any account's
     *            secret.
     *
     * @return void
     */
    public function test_second_account_secret_also_verifies()
    {
        CorsightAccount::factory()->create([
            'username' => 'first-user',
            'endpoint_secret' => 'first-secret',
        ]);
        CorsightAccount::factory()->create([
            'username' => 'second-user',
            'endpoint_secret' => 'second-secret',
        ]);
        CorsightDevice::factory()->create(['name' => 'Second Room']);
        $body = $this->eventBody('event-7', 'appearance', 'camera-777', 'Second Room');

        $response = $this->postSigned($body, $this->sign($body, 'second-secret'));

        $response->assertStatus(204);
        $this->assertDatabaseHas('corsight_device_data', [
            'event_id' => 'event-7',
            'camera_id' => 'camera-777',
        ]);
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> test_storage_disabled_device_is_not_stored
     * <Function> Check that a registered device with data storage disabled
     *            acks the event but does not store it.
     *
     * @return void
     */
    public function test_storage_disabled_device_is_not_stored()
    {
        $this->accountWith('test-webhook-secret');
        $device = CorsightDevice::factory()->create(['name' => 'Conference Room', 'enable_data_storage' => false]);
        $body = $this->eventBody('event-8', 'appearance', 'camera-8', 'Conference Room');

        $response = $this->postSigned($body, $this->sign($body, 'test-webhook-secret'));

        $response->assertStatus(204);
        $this->assertDatabaseCount('corsight_device_data', 0);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> test_event_without_camera_label_is_dropped
     * <Function> Check that an event whose camera cannot be resolved to a
     *            device is silently dropped (no diary row).
     *
     * @return void
     */
    public function test_event_without_camera_label_is_dropped()
    {
        $this->accountWith('test-webhook-secret');
        CorsightDevice::factory()->create(['name' => 'Conference Room']);
        $body = $this->eventBody('event-9', 'appearance', 'camera-9');

        $response = $this->postSigned($body, $this->sign($body, 'test-webhook-secret'));

        $response->assertStatus(204);
        $this->assertDatabaseCount('corsight_device_data', 0);
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> appearanceBody
     * <Function> Build a full appearance event body with the fields the
     *            person session pipeline consumes.
     *
     * @param  string  $eventId
     * @param  string  $appearanceId
     * @param  int  $trigger
     * @param  float  $started
     * @param  float  $sent
     * @param  string  $cameraDescription
     * @param  array  $crop
     * @return string
     */
    private function appearanceBody(string $eventId, string $appearanceId, int $trigger, float $started, float $sent, string $cameraDescription = 'Conference Room', array $crop = []): string
    {
        return json_encode([
            'msg' => 'test-event',
            'event_type' => 'appearance',
            'event_id' => $eventId,
            'msg_send_timestamp' => $sent,
            'trigger' => $trigger,
            'camera_data' => [
                'camera_id' => 'camera-123',
                'camera_description' => $cameraDescription,
            ],
            'appearance_data' => [
                'appearance_id' => $appearanceId,
                'utc_time_started' => $started,
            ],
            'crop_data' => $crop,
        ]);
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> test_appearance_lifecycle_creates_and_closes_session
     * <Function> Check that a trigger 0 event opens a session and a trigger
     *            2 event closes it with the correct dwell time.
     *
     * @return void
     */
    public function test_appearance_lifecycle_creates_and_closes_session()
    {
        $this->accountWith('test-webhook-secret');
        $device = CorsightDevice::factory()->create(['name' => 'Conference Room']);

        $start = $this->appearanceBody('evt-a1', 'app-1', 0, 1000.0, 1001.0);
        $this->postSigned($start, $this->sign($start, 'test-webhook-secret'))->assertStatus(204);

        $this->assertDatabaseCount('corsight_person_session', 1);
        $this->assertDatabaseHas('corsight_person_session', [
            'device_id' => $device->id,
            'appearance_id' => 'app-1',
            'status' => 'in',
        ]);

        $end = $this->appearanceBody('evt-a2', 'app-1', 2, 1000.0, 1300.0);
        $this->postSigned($end, $this->sign($end, 'test-webhook-secret'))->assertStatus(204);

        $this->assertDatabaseCount('corsight_person_session', 1);
        $this->assertDatabaseHas('corsight_person_session', [
            'appearance_id' => 'app-1',
            'status' => 'exited',
            'dwell_seconds' => 300,
        ]);
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> test_camera_status_display_mode_abandons_open_sessions
     * <Function> Check that a camera_status event with a non-active status
     *            (e.g. 11 = display mode) abandons the device's open
     *            sessions.
     *
     * @return void
     */
    public function test_camera_status_display_mode_abandons_open_sessions()
    {
        $this->accountWith('test-webhook-secret');
        CorsightDevice::factory()->create(['name' => 'Conference Room']);

        $start = $this->appearanceBody('evt-c1', 'app-c', 0, 1000.0, 1001.0);
        $this->postSigned($start, $this->sign($start, 'test-webhook-secret'))->assertStatus(204);

        $statusBody = json_encode([
            'msg' => 'camera-status',
            'event_type' => 'camera_status',
            'event_id' => 'evt-c2',
            'msg_send_timestamp' => 1500.5,
            'camera_data' => [
                'camera_id' => 'camera-123',
                'camera_description' => 'Conference Room',
            ],
            'update_data' => [
                'status' => 11,
                'status_str' => 'display',
            ],
        ]);

        $this->postSigned($statusBody, $this->sign($statusBody, 'test-webhook-secret'))->assertStatus(204);

        $this->assertDatabaseHas('corsight_person_session', [
            'appearance_id' => 'app-c',
            'status' => 'abandoned',
        ]);
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> test_duplicate_event_does_not_mutate_session_twice
     * <Function> Check that a replayed appearance event (same event_id) does
     *            not bump the session counters a second time.
     *
     * @return void
     */
    public function test_duplicate_event_does_not_mutate_session_twice()
    {
        $this->accountWith('test-webhook-secret');
        CorsightDevice::factory()->create(['name' => 'Conference Room']);

        $body = $this->appearanceBody('evt-d1', 'app-d', 3, 1000.0, 1001.0);
        $signature = $this->sign($body, 'test-webhook-secret');

        $this->postSigned($body, $signature)->assertStatus(204);
        $this->postSigned($body, $signature)->assertStatus(204);

        $session = CorsightPersonSession::where('appearance_id', 'app-d')->first();

        $this->assertNotNull($session);
        $this->assertSame(1, $session->event_count);
        $this->assertDatabaseCount('corsight_person_session', 1);
    }
}
