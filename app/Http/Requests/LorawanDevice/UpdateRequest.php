<?php

namespace App\Http\Requests\LorawanDevice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize    (1.0) Determine if the user is authorized to make this
    //                    request.
    // rules        (2.0) Get the validation rules that apply to the request.
    // computed     (3.0) Get validated data and attach online depending if
    //                    room is set.
    // attributes   (4.0) Get custom attributes for validator errors.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return boolean
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
     * @property \App\Models\LorawanDevice $device

     *
     * @return array
     */
    public function rules(Request $request): array
    {
        return [
            'name' => 'required|string',
            'dev_eui' => [
                'required',
                'size:16',
                'regex:/^[a-fA-F0-9]+$/u',
                Rule::unique('lorawan_devices', 'dev_eui')->ignore($request->route('device')->id)
            ],
            'manufacturer_id' => 'required',
            'model' => 'required',
            'gateway_id' => 'required',
            'room_area_id' => 'nullable|exists:room_areas,id',
            'room_id' => 'required',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> computed
     * <Function> Get validated data and attach online depending if room is set.
     *
     * @return array
     */
    public function computed(): array
    {
        return $this->validated();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> attributes
     * <Function> Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'Device Name',
            'dev_eui' => 'Device EUI',
            'manufacturer_id' => 'Manufacturer',
            'model' => 'Model',
            'gateway_id' => 'Gateway IP address',
            'room_id' => 'Room',
            'room_area_id' => 'Room Area',
        ];
    }
}
