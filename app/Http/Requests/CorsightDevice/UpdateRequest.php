<?php

namespace App\Http\Requests\CorsightDevice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize         (1.0) Determine if the user is authorized to make this
    //                         request.
    // rules             (2.0) Get the validation rules that apply to the
    //                         request.
    // attributes        (3.0) Get custom attributes for validator errors.

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
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('corsight_device', 'name')->ignore($this->route('device'))],
            'room_id' => 'nullable|exists:rooms,id',
            'room_area_id' => 'nullable|exists:room_areas,id',
            'role' => 'required|in:general,entry_point,exit_point,passage',
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
            'room_id' => 'room',
            'room_area_id' => 'room area',
        ];
    }
}
