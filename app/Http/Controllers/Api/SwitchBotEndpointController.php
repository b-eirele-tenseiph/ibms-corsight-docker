<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SwitchBot\EndpointRequest;
use App\Jobs\SwitchBot\HandleSwitchBotKeypadData;
use App\Jobs\SwitchBot\HandleWebhookData;

class SwitchBotEndpointController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __invoke         (1.0) Handle the incoming webhook request from SwitchBot.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __invoke
     * <Function> Handle the incoming webhook request from SwitchBot.
     *
     * @param  \App\Http\Requests\Api\SwitchBot\EndpointRequest  $request
     */
    public function __invoke(EndpointRequest $request)
    {
        $device = $request->device();
        $payload = $request->payload();

        if (!$device) {
            return response()->json(['message' => 'Device not registered.'], 200);
        }

        if ($request->isDeviceKeypadTouch($device) && $request->isKeypadCommandSuccessful()) {
            // Process keypad data
            HandleSwitchBotKeypadData::dispatch($device, $request->map());
        }

        HandleWebhookData::dispatch($device, $payload);

        return response()->noContent();
    }
}
