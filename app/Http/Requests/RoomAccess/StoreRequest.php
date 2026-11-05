<?php

namespace App\Http\Requests\RoomAccess;

use App\Models\SwitchBotLockAccess;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // START_TIME                  (1.0)  Default start time for scheduled
    //                                     access.
    // END_TIME                    (2.0)  Default end time for scheduled access.
    // authorize                   (3.0)  Determine if the user is authorized to
    //                                     make this request.
    // rules                       (4.0)  Get the validation rules that apply to
    //                                     the request.
    // withValidator               (5.0)  Attach additional rules on validator
    //                                    instance.
    // checkDates                  (6.0)  Check if date is valid by finding Room
    //                                    Access conflict.
    // checkEndTime                (7.0)  Check if check out time is valid by
    //                                     finding Room Access conflict.
    // checkStartTime              (8.0)  Check if check in time is valid by
    //                                     finding Room Access conflict.
    // userData                    (9.0)  Get the data for user.
    // roomAccessData              (10.0) Get the data for room access.
    // switchbotLockAccessData     (11.0) Get the data for switchBot lock access.
    // generatePasscodeName        (12.0) Generate passcode name for switch bot
    //                                     lock access.

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return boolean
     */
    public function authorize()
    {
        return true;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name'         => 'required|string',
            'username'     => 'required|string|unique:users,username',
            'email'        => 'nullable|email|unique:users,email',
            'pin'          => 'required|digits:6',
            'is_permanent' => 'boolean',
        ];

        if (!$this->input('is_permanent')) {
            $rules += [
                'start_date' => 'required|date|after_or_equal:today',
                'end_date'   => 'required|date|after:start_date',
                'start_time' => 'required',
                'end_time'   => 'required',
            ];
        }

        return $rules;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> withValidator
     * <Function> Attach additional rules on validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $startTime = $this->input('start_time');
            $endDate = $this->input('end_date');
            $endTime = $this->input('end_time');

            $userTimezone = request()->cookie('timezone') ?? config('timezone.default');

            if ($startDate && $startTime && $endDate && $endTime) {
                $start = Carbon::parse("{$startDate} {$startTime}", $userTimezone)->tz('UTC');
                $end = Carbon::parse("{$endDate} {$endTime}", $userTimezone)->tz('UTC');

                if ($start->lte(now()->addHour())) {
                    $validator->errors()->add('start_time', __('validation.custom.start_time_minimum'));
                }

                if ($end->lte($start->copy()->addDay())) {
                    $validator->errors()->add('end_time', __('validation.custom.end_time_minimum'));
                }
            }
        });
    }


    /**
     * <Layer number> (9.0)
     *
     * <Processing name> userData
     * <Function> Get the data for user.
     *
     * @return array
     */
    public function userData()
    {
        return [
            'name'                 => $this->name,
            'username'             => $this->username,
            'email'                => $this->email,
            'password'             => Hash::make($this->pin),
            'password_displayable' => $this->pin
        ];
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> roomAccessData
     * <Function> Get the data for room access.
     *
     * @return array
     */
    public function roomAccessData()
    {
        $userTimezone = request()->cookie('timezone') ?? config('timezone.default');

        $data = [
            'room_id' => $this->room->id,
            'is_permanent' => $this->is_permanent,
        ];

        // Remove values for dates if its marked permanent
        if (!$this->is_permanent) {
            $startAccess = Carbon::parse(
                "{$this->start_date} {$this->start_time}",
                $userTimezone
            )->tz('UTC');

            $endAccess = Carbon::parse(
                "{$this->end_date} {$this->end_time}",
                $userTimezone
            )->tz('UTC');

            $data = array_merge($data, [
                'start_date' => $startAccess,
                'end_date'   => $endAccess,
            ]);
        }

        return $data;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> switchbotLockAccessData
     * <Function> Get the data for switchBot lock access.
     *
     * @return array
     */
    public function switchbotLockAccessData(): array
    {
        $data = [
            'passcode_id' => -1,
            'name' =>  $this->generatePasscodeName(),
            'access_pin' => $this->pin,
            'access_type' => $this->is_permanent ? 'permanent' : 'timeLimit',
            'status' => 'for creation',
        ];

        if (!$this->is_permanent) {
            $data = array_merge(
                $data,
                [
                    'arrival_time' => $this->roomAccessData()['start_date'],
                    'departure_time' => $this->roomAccessData()['end_date'],
                ]
            );
        }

        return $data;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> generatePasscodeName
     * <Function> Generate passcode name for switch bot lock access.
     *
     * @return string
     */
    private function generatePasscodeName(): string
    {
        $generatedName = '';

        do {
            $dateString = now()->format('YmdHi');
            $generatedName = "iBMS-{$this->name}-{$dateString}";
        } while (SwitchBotLockAccess::where('name', $generatedName)->exists());

        return $generatedName;
    }
}
