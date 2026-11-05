<?php

namespace App\Imports;

use App\Models\LorawanDevice;

class LorawanDevicesImport extends BaseImport
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // $headers                  (1.0) Summary of headers
    // model                     (2.0) Return the model to be imported
    // rules                     (3.0) Get the validation rules that apply
    //                                 to the import.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> headers
     * <Function> Summary of headers
     * @var array
     */
    public array $headers = ['room_name', 'room_area_name', 'device', 'dev_eui',  'model', 'gateway_ip'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> model
     * <Function> Create LorawanDevice model based on the given row.
     *
     * @param  array  $row
     * @return \App\Models\LorawanDevice
     */
    public function model(array $row)
    {
        $gateway  = $this->service->findGateway($row);
        $room     = $this->service->findRoom($row);

        $roomArea = null;
        if (!empty($row['room_area_name'])) {
            $roomArea = $this->service->findRoomArea($row, $room);
        }

        $this->rowsAffected++;

        return new LorawanDevice([
            'room_id'      => $room->id,
            'room_area_id' => $roomArea?->id,
            'name'         => $row['device'],
            'dev_eui'      => $row['dev_eui'],
            'model'        => $row['model'],
            'gateway_id'   => $gateway->id,
            'online_flag'  => false,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the import.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'room_name'      => 'required',
            'room_area_name' => 'nullable|exists:room_areas,name',
            'device'         => 'required|string',
            'dev_eui'        => 'required|max:16|min:16|regex:/^[a-fA-F0-9]+$/u|unique:lorawan_devices,dev_eui',
            'model'          => 'required|exists:manufacturer_models,name',
            'gateway_ip'     => 'required|exists:lorawan_gateways,ip_address',
        ];
    }
}
