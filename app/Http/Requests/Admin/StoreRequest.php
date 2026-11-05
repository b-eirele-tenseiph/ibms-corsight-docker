<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize       (1.0) Determine if the user is authorized to make this
    //                       request.
    // rules           (2.0) Get the validation rules that apply to the request.
    // validatedAdmin  (3.0) Get validated admin data and attached password hash.
    // validatedRole   (4.0) Get validate role of admin from request.

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
            'name' => 'required|string',
            'username' => 'required|string|unique:admins,username',
            'password' => 'required|min:6',
            'email' => 'nullable|email|unique:admins,email',
            'role' => 'required|not_in:super admin'
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> validatedAdmin
     * <Function> Get validated admin data and attached password hash.
     *
     * @return array
     */
    public function validatedAdmin(): array
    {
        // Parse validated form except for role
        $validated = $this->safe()->except(['role']);

        // Hashing the password
        $validated['password'] = Hash::make($validated['password']);

        // Set Default Value of enabled to false
        $validated['enabled'] = false;

        // Return modified validated form
        return $validated;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> validatedRole
     * <Function> Get validate role of admin from request.
     *
     * @return string
     */
    public function validatedRole(): string
    {
        // Parse validated form except for role
        $validated = $this->safe();

        return $validated['role'];
    }
}
