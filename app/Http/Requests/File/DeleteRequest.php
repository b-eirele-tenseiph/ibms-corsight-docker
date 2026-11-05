<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize    (1.0) Determine if the user is authorized to make this
    //                    request.
    // rules        (2.0) Get the validation rules that apply to the request.
    // messages     (3.0) Get custom error messages for validation rules.

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
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> messages
     * <Function> Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'confirmation.required' => __('validation.custom.file_confirmation'),
            'confirmation.in' => __('validation.custom.file_name_mismatch'),
        ];
    }
}
