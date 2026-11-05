<?php

namespace App\Http\Requests\User;

use App\Models\SwitchBotLockAccess;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class UpdateRequest extends FormRequest
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
    // roomAccessData              (4.0) Get the data of room access.
    // room                        (5.0) Get room model instance base on room id
    //                                   from request
    // withValidator               (6.0) Validates that end date is not before 
    //                                   start date, and if the dates are the 
    //                                   same,end time must be after start time.
    // switchbotLockAccessData     (7.0) Get the data for switchBot lock access.
    // generatePasscodeName        (8.0) Generate passcode name for switch bot
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
            'name' => 'string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'email' => 'email',
            'password' => 'required|max_digits:6|min_digits:6',
            'password_displayable' => 'required|max_digits:6|min_digits:6',
            'is_permanent' => 'required|boolean',
            'room_id' => 'required|string|exists:rooms,id',
            'start_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
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
    public function userData()
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password_displayable),
            'password_displayable' => $this->password_displayable
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> roomAccessData
     * <Function> Get the data for room access.
     *
     * @return array
     */
    public function roomAccessData()
    {
        // Parse validated form
        $validated = $this->validated();

        $userTimeZone = request()->cookie('timezone');

        if (empty($userTimeZone) || strtolower($userTimeZone) === 'null') {
            $userTimeZone = config('timezone.default');
        }

        $data = [
            'room_id' => $this->room_id,
            'is_permanent' => $this->is_permanent,
        ];

        // Remove values for dates if its marked permanent
        if (!$this->is_permanent) {

            $startAccess = Carbon::createFromFormat(
                "Y-m-d H:i",
                "{$validated['start_date']} {$validated['start_time']}",
                $userTimeZone
            )->tz('UTC');

            $endAccess = Carbon::createFromFormat(
                "Y-m-d H:i",
                "{$validated['end_date']} {$validated['end_time']}",
                $userTimeZone
            )->tz('UTC');

            $data = array_merge($data, [
                'start_date' => $startAccess,
                'end_date'   => $endAccess,
            ]);
        } else {
            // Force null values when marked as permanent
            $data['start_date'] = null;
            $data['end_date'] = null;
        }

        return $data;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> room
     * <Function> Get room model instance base on room id from request.
     *
     * @return \App\Models\Room
     */
    public function room()
    {
        return $this->user->room;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> withValidator
     * <Function> Validates that end date is not before start date, and if
     *            the dates are the same, end time must be after start time.
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
                $start = Carbon::createFromFormat('Y-m-d H:i', "{$startDate} {$startTime}", $userTimezone)->tz('UTC');
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
     * <Layer number> (7.0)
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
            'name'  => $this->generatePasscodeName(),
            'access_type' =>  $this->is_permanent ? 'permanent' : 'timeLimit',
            'access_pin'  => $this->password_displayable,
            'status' => 'for creation',
        ];

        if (!$this->is_permanent) {
            $data = array_merge(
                $data,
                [
                    'arrival_time' => Carbon::createFromFormat("Y-m-d H:i", "{$this->start_date} {$this->start_time}", 'Asia/Manila')->tz('UTC'),
                    'departure_time' => Carbon::createFromFormat("Y-m-d H:i", "{$this->end_date} {$this->end_time}", 'Asia/Manila')->tz('UTC'),
                ]
            );
        }

        return  $data;
    }

    /**
     * <Layer number> (8.0)
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
