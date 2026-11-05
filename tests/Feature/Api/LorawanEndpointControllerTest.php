<?php

namespace Tests\Feature\Api;

use App\Jobs\Lorawan\HandleUplinkDataDefault;
use App\Models\LorawanDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\Data;

class LorawanEndpointControllerTest extends TestCase
{
    use RefreshDatabase, Data;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // credentials                                                         (1.0)
    // test_actility_dev_eui_is_valid_and_processed                        (2.0)
    // test_actility_payload_is_valid_and_processed                        (3.0)
    // test_invoke_method_with_filled_data_are_valid                       (4.0)
    // test_lorawan_uplink_has_been_decoded                                (5.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> credentials
     * <Function> Create data for dev_eui and random payload.
     *
     * @return array
     */
    private function credentials(): array
    {
        $data = $this->createLorawanDeviceData();

        return [
            'dev_eui' => $data->device->dev_eui,
            'payload' => bin2hex(random_bytes(32)),
        ];
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_actility_dev_eui_is_valid_and_processed
     * <Function> Check if the dev eui is processed.
     *
     * @return void
     */
    public function test_actility_dev_eui_is_valid_and_processed()
    {
        Queue::fake();

        $this->credentials();

        $data = $this->createLorawanDeviceData();

        $response = $this->postJson(
            route('lorawan-endpoint'),
            ['DevEUI_uplink' => ['DevEUI' => $data->dev_eui]],
            ['user-agent'    => 'ACTILITY-LRCLRN-DEVICE-AGENT/1.0']
        );

        $response->assertStatus(200);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_actility_payload_is_valid_and_processed
     * <Function> Check if the payload is processed.
     *
     * @return void
     */
    public function test_actility_payload_is_valid_and_processed(): void
    {
        Queue::fake();

        $response = $this->postJson(
            route('lorawan-endpoint'),
            ['DevEUI_uplink' => ['payload_hex' => $this->credentials()['payload']]],
            ['user-agent' => 'ACTILITY-LRCLRN-DEVICE-AGENT/1.0']
        );

        $response->assertStatus(200);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_invoke_method_with_filled_data_are_valid
     * <Function> Check if the dev eui and payload is processed.
     *
     * @return void
     */
    public function test_invoke_method_with_filled_data_are_valid(): void
    {
        Queue::fake();

        $data = $this->createLorawanDeviceData();
        $this->credentials();
        $response = $this->postJson(route('lorawan-endpoint'), [
            'DevEUI_uplink' => [
                'DevEUI' => $data->dev_eui,
                'payload_hex' => $this->credentials()['payload'],
            ],
        ], ['user-agent' => 'ACTILITY-LRCLRN-DEVICE-AGENT/1.0']);

        $response->assertStatus(200);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_lorawan_uplink_has_been_decoded
     * <Function> Check if the job for handlingUplinkData is working as expected.
     *
     * @return void
     */
    public function test_lorawan_uplink_has_been_decoded(): void
    {
        Bus::fake();

        $this->test_invoke_method_with_filled_data_are_valid();
        $device = LorawanDevice::factory()->create();

        dispatch(new HandleUplinkDataDefault($device, $this->credentials()['payload'], false));

        // Assert that a job was dispatched
        Bus::assertDispatched(HandleUplinkDataDefault::class);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_lorawan_uplink_has_not_been_decoded
     * <Function> Check if the job for handlingUplinkData is not working as
     *           expected if there is no payload and dev eui.
     *
     * @return void
     */
    public function test_lorawan_uplink_has_not_been_decoded()
    {
        Bus::fake();

        $response = $this->postJson(route('lorawan-endpoint'), [
            'DevEUI_uplink' => [],
        ], ['user-agent' => 'ACTILITY-LRCLRN-DEVICE-AGENT/1.0']);

        $response->assertStatus(200);

        // Assert that a job was not dispatched
        Bus::assertNotDispatched(HandleUplinkDataDefault::class);
    }
}
