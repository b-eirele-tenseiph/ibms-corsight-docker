<?php

namespace App\Imports;

use App\Models\LorawanGateway;
use Illuminate\Validation\Rule;

class LorawanGatewaysImport extends BaseImport
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // $headers                     (1.0) Summary of headers
    // model                        (2.0) Return the model to be imported
    // rules                        (3.0) Get the validation rules that apply
    //                                    to the import.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> headers
     * <Function> Summary of headers
     * @var array
     */
    public array $headers = ['ip_address', 'mac_address', 'floor_name', 'connection'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> model
     * <Function> Create LorawanGateway model based on the given row.
     *
     * @param  array  $row
     * @return \App\Models\LorawanGateway
     */
    public function model(array $row)
    {
        $floor = $this->service->findFloor($row);
        $this->rowsAffected++;

        return new LorawanGateway([
            'ip_address'  => $row['ip_address'],
            'mac_address' => $row['mac_address'],
            'floor_id'    => $floor->id,
            'connection'  => $row['connection'],
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
            'ip_address'  => 'required|ip|unique:lorawan_gateways,ip_address',
            'mac_address' => 'required|mac_address|unique:lorawan_gateways,mac_address',
            'floor_name'  => 'required|exists:floors,name',
            'connection'  => ['required', Rule::in(array_keys(config('lorawan.connections')))],
        ];
    }
}
