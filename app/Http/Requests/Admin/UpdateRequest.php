<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize       (1.0) Determine if the user is authorized to make this
    //                       request.
    // rules           (2.0) Get the validation rules that apply to the request.
    // messages        (3.0) Get custom messages for validator errors.
    // validatedAdmin  (4.0) Get validated admin data and attached password hash
    //                       if changed.
    // validatedRole   (5.0) Get validate role of admin from request.

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
            'name'            => 'required|string',
            'username'        => [
                'required',
                Rule::unique('admins')->ignore($this->admin)
            ],
            'change_password' => 'boolean',
            'password'        => 'nullable|required_if:change_password,true|min:6',
            'email'           => 'nullable|email|unique:admins,email,' . $this->admin->id,
            'role'            => 'required|exists:roles,name|not_in:super admin'
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> messages
     * <Function> Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'password.required_if' => __('validation.custom.password_required'),
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> validatedAdmin
     * <Function> Get validated admin data and attached password hash,
     *            if changed.
     *
     * @return array
     */
    public function validatedAdmin(): array
    {
        // Parse validated form
        $validated = $this->safe()->except(['role', 'password']);

        // Hashing the password
        if (isset($this->password)) {
            $validated['password'] = Hash::make($this->password);
            $validated['password_last_updated_at'] = now();
        }

        // Return modified validated form
        return $validated;
    }

    /**
     * <Layer number> (5.0)
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
