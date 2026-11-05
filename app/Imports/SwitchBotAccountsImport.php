<?php

namespace App\Imports;

use App\Models\SwitchBotAccount;
use Illuminate\Support\Facades\Http;

class SwitchBotAccountsImport extends BaseImport
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // $headers                     (1.0) Summary of headers
    // model                        (2.0) Return the model to be imported
    // rules                        (3.0) Get the validation rules that apply
    //                                    to the import.
    // customValidationMessages     (4.0) Get the custom validation messages
    //                                    for the import.
    // withValidator                (5.0) Attach conditional validation
    //                                    to the validator.
    // checkAccessToken             (6.0) Check if access token is valid.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $headers
     * <Function> The headers that should be included in the file.
     *
     * @var array
     */
    public array $headers = [
        'room_name',
        'email',
        'password',
        'access_token',
        'secret_key',
    ];


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> model
     * <Function> Create SwitchBot Account model based on the given row.
     *
     * @param  array $row
     * @return \App\Models\SwitchBotAccount
     */
    public function model(array $row)
    {
        // Retrieve Room base on Room name given
        $room = $this->service->findRoom($row);

        // Increment rows affected counter
        $this->rowsAffected++;

        return
            new SwitchBotAccount(
                [
                    'room_id'       =>  optional($room)->id,
                    'email'         =>  $row['email'],
                    'password'      =>  $row['password'],
                    'access_token'  =>  $row['access_token'],
                    'secret_key'    =>  $row['secret_key'],
                    'last_synced_at'  =>  null,
                    'created_at'    =>  now(),
                ]
            );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the importable row.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'room_name'     => ['required', 'exists:rooms,name'],
            'email'         => ['required', 'email', 'unique:switch_bot_accounts,email'],
            'password'      => ['required'],
            'access_token'  => ['required', 'alpha_num'],
            'secret_key'    => ['required', 'alpha_num'],
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> customValidationMessages
     * <Function> Get custom validation message for attribute and rule.
     *
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'room_name.exists' => __('import.switch_bot_accounts.room_name_exists'),
            'email.unique' => __('import.switch_bot_accounts.email_unique'),
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> withValidator
     * <Function> Attach conditional validation to the validator.
     *
     * @param  [laravel excel validator] $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->getData() as $key => $row) {
                // Ensure row number is integer
                $rowNumber = is_numeric($key) ? (int)$key : 2; // fallback to 2 if not numeric

                // Check for existing account
                $account = $this->service->findAccountUsingRoomName($row);

                $token = $row['access_token'] ?? null;
                $secret = $row['secret_key'] ?? null;

                // Validate token & secret
                if ($token && $secret && !$this->checkAccessTokenAndSecretKey($token, $secret)) {
                    $validator->errors()->add($rowNumber, __('import.switch_bot_accounts.token_invalid'));
                }

                // Validate if account already exists
                if ($account) {
                    $validator->errors()->add($rowNumber, __('import.switch_bot_accounts.room_already_has_account', ['room' => $row['room_name']]));
                }
            }
        });
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> checkAccessToken
     * <Function> Check if access token is valid.
     *
     * @return boolean
     */
    private function checkAccessTokenAndSecretKey($token, $secret): bool
    {
        $response = Http::switchBotHttp($token, $secret)->get('devices');

        return $response->successful();
    }
}
