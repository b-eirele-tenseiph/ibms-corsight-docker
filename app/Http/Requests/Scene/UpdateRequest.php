<?php

namespace App\Http\Requests\Scene;

use App\Models\LorawanDevice;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotRemote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{

    /******************************************************************************/
    /* Processing Hierarchy                                                       */
    /******************************************************************************/
    // authorize               (1.0) Determine if the user is authorized to make this
    //                               request.
    // rules                   (2.0) Get the validation rules that apply to the request.
    // attributes              (3.0) Get custom attributes for validator errors.
    // messages                (4.0) Get custom error messages for validation rules.
    // prepareForValidation    (5.0) Prepares and formats input data before validation.
    // computed                (6.0) Computes command data, finds associated devices,
    //                               and validates input.


    /**
     * <Layer number> (1.0)
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
        return [
            'context' => 'sometimes|array',
            'color' => 'required',
            'icon' => 'required',
            'name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('scenes', 'name')
                    ->ignore($this->scene->id)
                    ->where('room_id', $this->room_id),
            ],
            'room_id' => 'required|exists:rooms,id',
            'commands' => 'required|array',
            'commands.*.id' => 'required',
            'commands.*.command' => 'required',
            'commands.*.type' => 'required',
            'scheduled' => 'required',
            'scheduleType' => 'required',
            'days'   => 'nullable|required_if:scheduleType,custom|array',
            'days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'time' => 'nullable|required_if:scheduled,true',
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
            'color' => 'Scene Color',
            'icon' => 'Scene Icon',
            'name' => 'Scene Name',
            'room_id' => 'Room Name',
            'commands' => 'Commands',
            'commands.*.id' => 'Command ID',
            'commands.*.command' => 'Command',
            'commands.*.type' => 'Command Type',
            'time' => 'Time',
            'days' => 'Days',
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> messages
     * <Function> Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'commands.required' => __('validation.custom.commands_required'),
            'device_type.in' => __('validation.custom.invalid_device_type'),
            'commands.array' => __('validation.custom.commands_valid_array'),
        ];
    }

    /**
     * <Layer Number> (5.0)
     * <Processing Name> prepareForValidation
     * <Function> Prepares and formats input data before validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'schedule_days' => is_string($this->schedule_days)
                ? json_decode($this->schedule_days, true) ?? []
                : (is_array($this->schedule_days) ? $this->schedule_days : []),
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> computed
     * <Function> Computes command data, finds associated devices, and validates input.
     *
     * @return array
     */
    public function computed()
    {
        $commands = collect($this->commands);

        $commands = $commands->map(function ($array) {
            $device = SwitchBotDevice::find($array['id'])
                ?? LorawanDevice::find($array['id'])
                ?? SwitchBotRemote::find($array['id']);

            $array["device"] = $device;

            return $array;
        });

        $data = $this->validated();

        $data['commands'] = $commands->toArray();

        return $data;
    }
}
