<?php

namespace App\Http\Requests\RoleImpersonation;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize       (1.0) Determine if the user is authorized to make this
    //                       request.
    // rules           (2.0) Get the validation rules that apply to the request.

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
        if (session()->has('impersonating_role')) {
            return session()->get('original_role') === 'super admin';
        }

        return auth()->user()->hasRole('super admin', null);
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
            'role' => 'required|string|exists:roles,name|not_in:super admin',
        ];
    }
}
