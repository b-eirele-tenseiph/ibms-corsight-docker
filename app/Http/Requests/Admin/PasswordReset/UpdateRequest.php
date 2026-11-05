<?php

namespace App\Http\Requests\Admin\PasswordReset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize        (1.0) Determine if the user is authorized to make
    //                        this request.
    // rules            (2.0) Get the validation rules that apply to
    //                        the request.
    // messages         (3.0) Get the error messages for the defined
    //                        validation rules.
    // attributes       (4.0) Get custom attributes for validator errors.

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'password' => [
                'required',

                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'password_confirmation' => [
                'required',
                'same:password', // This links the error to this specific field
            ],
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> messages
     * <Function> Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // This targets the 'confirmed' rule on the 'password' field
            'password.required' => __('validation.required', ['attribute' => 'password']),
            'password_confirmation.same' => __('validation.custom.password_mismatch'),
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> attributes
     * <Function> Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation',
        ];
    }
}
