<?php

namespace App\Http\Requests\User;

use App\Models\Room;
use App\Models\SwitchBotLockAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // authorize                   (1.0) Determine if the user is authorized to
    //                                   make this request.
    // rules                       (2.0) Get the validation rules that apply
    //                                   to the request.
    // userData                    (3.0) Return the validated user data for 
    //                                   database.
    // attributes                  (4.0) Custom attribute names
    // roomAccessData              (5.0) Get the data of room access
    // room                        (6.0) Get room model instance base on room 
    //                                   id from request
    // withValidator               (7.0) Validates that end time is after start 
    //                                   time if dates match.
    // switchbotLockAccessData     (8.0) Get the data for switchBot lock
    //                                   access.
    // generatePasscodeName        (9.0) Generate passcode name for switch bot
    //                                   lock access.

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
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|digits:6|unique:switch_bot_lock_accesses,access_pin',
            'room_id' => 'required|string|exists:rooms,id',
            'start_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'nullable|date_format:Y-m-d|after:start_date',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s',
            'is_permanent'  => 'boolean'
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> userData
     * <Function> Return the validated user data for database.
     *
     * @return array
     */
    public function userData(): array
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'password_displayable' => $this->password,
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> attributes
     * <Function> Custom attribute names.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'room_id'       => 'Room',
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> roomAccessData
     * <Function> Get the data of room access
     *
     * @return array
     */
    public function roomAccessData()
    {
        $userTimeZone = request()->cookie('timezone');

        if (empty($userTimeZone) || strtolower($userTimeZone) === 'null') {
            $userTimeZone = config('timezone.default');
        }

        $data = [
            'room_id' => $this->room_id,
            'is_permanent' => $this->is_permanent,
        ];

        if (!$this->is_permanent) {
            $startAccess = Carbon::createFromFormat(
                "Y-m-d H:i:s",
                "{$this->start_date} {$this->start_time}",
                $userTimeZone
            )->tz('UTC');

            $endAccess = Carbon::createFromFormat(
                "Y-m-d H:i:s",
                "{$this->end_date} {$this->end_time}",
                $userTimeZone
            )->tz('UTC');

            $data = array_merge($data, [
                'start_date' => $startAccess,
                'end_date'   => $endAccess,
            ]);
        }

        return $data;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> room
     * <Function> Get room model instance base on room id from request.
     *
     * @return \App\Models\Room
     */
    public function room()
    {
        return Room::find($this->room_id);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> withValidator
     * <Function> Validates that end time is after start time if dates match.
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $startTime = $this->input('start_time');
            $endDate = $this->input('end_date');
            $endTime = $this->input('end_time');

            $userTimezone = request()->cookie('timezone') ?? config('timezone.default');

            if ($startDate && $startTime) {
                $start = Carbon::createFromFormat('Y-m-d H:i:s', "{$startDate} {$startTime}", $userTimezone)->tz('UTC');
                $now = Carbon::now();

                if ($start->lessThan($now)) {
                    $validator->errors()->add('start_time', __('validation.custom.start_time_past'));
                }
            }

            if ($startDate && $startTime && $endDate && $endTime) {
                $start = Carbon::parse("{$startDate} {$startTime}");
                $end = Carbon::parse("{$endDate} {$endTime}");

                if ($end->lessThanOrEqualTo($start->copy()->addHours(3))) {
                    $validator->errors()->add('end_time', __('validation.custom.end_time_minimum'));
                }
            }
        });
    }


    /**
     * <Layer number> (8.0)
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
            'access_pin' => $this->password_displayable,
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
     * <Layer number> (9.0)
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
