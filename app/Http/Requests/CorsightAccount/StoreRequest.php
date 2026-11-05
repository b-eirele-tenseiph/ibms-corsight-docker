<?php

namespace App\Http\Requests\CorsightAccount;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'username' => 'required|string|max:255|unique:corsight_account,username',
            'password' => 'required|string',
            'endpoint_secret'    => 'required|string',
            'endpoint_url'       => 'required|url',
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
            'username' => 'Fortify username',
            'password' => 'Fortify password',
            'endpoint_secret'    => 'endpoint secret',
            'endpoint_url'       => 'endpoint URL',
        ];
    }
}
