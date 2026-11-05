<?php

namespace App\Services\SwitchBotDevice\SwitchBotKeypad;

use App\Models\SwitchBotDevice;
use Illuminate\Support\Facades\Http;

class SwitchBotKeypadService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createPasscode       (1.0) Send api request to create keypad passcode.
    // deletePasscode       (2.0) Send api request to delete keypad passcode.
    // fetchPasscodeList    (3.0) Send api request to retrieve keylist of keypad.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createPasscode
     * <Function> Send api request to create keypad passcode.
     *
     * @return array
     */
    public function createPasscode(SwitchBotDevice $device, array $data): array
    {
        $responseData = [];

        // Send api request for creating key
        $response = Http::switchBotHttp($device->room->switchBotAccount->access_token, $device->room->switchBotAccount->secret_key)
            ->post("/devices/{$device->serial_number}/commands", $data);

        if ($response->successful() && $response->json('statusCode') == 100) {
            $responseData = $response->json('body');
        }

        return $responseData;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> deletePasscode
     * <Function> Send api request to delete keypad passcode.
     *
     * @return array
     */
    public function deletePasscode(SwitchBotDevice $device, array $data): array
    {
        $responseData = [];

        // Send api request for deleting key
        $response = Http::switchBotHttp($device->room->switchBotAccount->access_token, $device->room->switchBotAccount->secret_key)
            ->post("/devices/{$device->serial_number}/commands", $data);

        if ($response->successful() && $response->json('statusCode') == 100) {
            $responseData = $response->json('body');
        }

        return $responseData;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> fetchPasscodeList
     * <Function> Send api request to retrieve keylist of keypad.
     *
     * @return array
     */
    public function fetchPasscodeList(SwitchBotDevice $device)
    {
        // Retrieve devices via api
        $response = Http::switchBotHttp($device->room->switchBotAccount->access_token, $device->room->switchBotAccount->secret_key)
            ->get("/devices");

        if ($response->failed() && $response->json('statusCode') != 100) {
            return null;
        }

        $deviceList = $response->json('body.deviceList');
        // Collect keypad list from keypad data if found.
        $keypadData = collect($deviceList)->firstWhere('deviceId', $device->serial_number);

        return $keypadData['keyList'] ?? [];
    }
}
