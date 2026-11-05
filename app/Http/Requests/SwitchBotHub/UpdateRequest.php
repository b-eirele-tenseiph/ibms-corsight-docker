<?php

namespace App\Http\Requests\SwitchBotHub;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize                 (1.0) Determine if the user is authorized to
    //                                 make this request.
    // rules                     (2.0) Get the validation rules that apply to
    //                                 the request.

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_area_id' => 'nullable|exists:room_areas,id',
            'name'         => 'required|string|max:255',
        ];
    }
}
