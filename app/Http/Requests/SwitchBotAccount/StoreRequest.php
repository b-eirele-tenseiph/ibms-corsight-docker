<?php

namespace App\Http\Requests\SwitchBotAccount;

use App\Rules\ValidDomain;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

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
    // checkAccessToken  (4.0) Check if access token and secret key are valid.
    // AndSecretKey
    // withValidator     (5.0) Attach additional rules on validator instance.
    // validatedWebhook  (6.0) Get validated webhook data.
    // validatedData     (7.0) Get validated data.

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
            'room_id'           => 'required',
            'email'             => 'required|email|unique:switch_bot_accounts,email',
            'password'          => 'required',
            'access_token'      => 'required|alpha_num',
            'secret_key'        => 'required|alpha_num',
            'webhook_action'    => 'in:setup,update',
            'webhook_url'       => 'required|url',
            new ValidDomain
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
            'room_id'      => 'room',
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> checkAccessTokenAndSecretKey
     * <Function> Check if access token and secret key are valid.
     *
     * @return boolean
     */
    private function checkAccessTokenAndSecretKey(): bool
    {
        return Http::switchBotHttp($this->access_token, $this->secret_key)
            ->get("/devices")
            ->successful();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> withValidator
     * <Function> Attach additional rules on validator instance.
     *
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (!$this->checkAccessTokenAndSecretKey()) {
                $validator->errors()->add('secret_key', __('validation.custom.invalid_access_token'));
            }
        });
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> validatedWebhook
     * <Function> Get validated webhook data.
     *
     * @return array
     */
    public function validatedWebhook(): array
    {
        // Extract only the relevant webhook fields from the validated data
        return $this->safe()->only(['webhook_action', 'webhook_url']);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> validatedData
     * <Function> Get validated data.
     *
     * @return array
     */
    public function validatedData(): array
    {
        return Arr::except($this->validated(), ['webhook_action', 'webhook_url']);
    }
}
