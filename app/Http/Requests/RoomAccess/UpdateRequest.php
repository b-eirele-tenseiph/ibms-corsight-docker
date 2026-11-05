<?php

namespace App\Http\Requests\RoomAccess;

use App\Models\Room;
use App\Models\SwitchBotLockAccess;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize                   (1.0)  Determine if the user is authorized to
    //                                    make this request.
    // rules                       (2.0)  Get the validation rules that apply to
    //                                    the request.
    // withValidator               (3.0)  Attach additional rules on validator
    //                                    instance.
    // room                        (4.0)  Get the room base on id present on
    //                                    request.
    // userData                    (5.0) Get the data for user.
    // roomAccessData              (6.0) Get the data for room access.
    // switchbotLockAccessData     (7.0) Get the data for switchBot lock access.
    // generatePasscodeName        (8.0) Generate passcode name for switch bot
    //                                     lock access.

    /**
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
     * <Layer number> (2.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'room_id'       => 'exists:rooms,id',
            'is_permanent'  => 'boolean'
        ];

        // If access isn't tagged as permanent, make dates required
        if (!$this->input('is_permanent')) {
            $rules = array_merge($rules, [
                'start_date'   => 'required|date|after_or_equal:today',
                'end_date'     => 'required|date|after:start_date',
                'start_time'   => 'required',
                'end_time'     => 'required',
            ]);
        }

        return $rules;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> withValidator
     * <Function> Attach additional rules on validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
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
                    $validator->errors()->add('start_time', __('validation.custom.start_time_minimum'));
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
     * <Layer number> (4.0)
     *
     * <Processing name> room
     * <Function> Get the room base on id present on request.
     *
     * @return \App\Models\Room
     */
    public function room()
    {
        return Room::find($this->room_id);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> userData
     * <Function> Get the data for user.
     *
     * @return array
     */
    public function userData()
    {
        return [
            'room_id' => $this->room_id
        ];
    }

    /**
     * <Layer number> (6.0)
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
            'name' =>  $this->generatePasscodeName(),
            'access_pin' => $this->access->user->password_displayable,
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
            $generatedName = "iBMS-{$this->access->user->name}-{$dateString}";
        } while (SwitchBotLockAccess::where('name', $generatedName)->exists());

        return $generatedName;
    }
}
