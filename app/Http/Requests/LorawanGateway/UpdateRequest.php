<?php

namespace App\Http\Requests\LorawanGateway;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize         (1.0) Determine if the user is authorized to make this 
    //                         request.
    // rules	         (2.0) Get the validation rules that apply to the 
    //                         request.
    // attributes        (3.0) Get custom attributes for validator errors.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ip_address' => [
                'required',
                'ip',
                Rule::unique('lorawan_gateways', 'ip_address')->ignore($this->route('gateway')->id),
            ],
            'mac_address' => [
                'required',
                'mac_address',
                Rule::unique('lorawan_gateways', 'mac_address')->ignore($this->route('gateway')->id),
            ],
            'connection' => [
                'required',
                Rule::in(array_keys(config('lorawan.connections'))),
            ],
            'floor_id' => 'required'
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> attributes
     * <Function> Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'Room Name',
            'floor_id' => 'Floor Name'
        ];
    }
}
