<?php

namespace App\Http\Requests\Scene;

use App\Models\LorawanDevice;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotRemote;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize    (1.0) Determine if the user is authorized to make this
    //                    request.
    // rules        (2.0) Get the validation rules that apply to the request.
    // attributes   (3.0) Get custom attributes for validator errors.
    // messages     (4.0) Get custom error messages for validation rules.
    // computed     (5.0) Computes command data, finds associated devices,
    //                    and validates input.

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
            'context' => 'sometimes|array',
            'color' => 'required',
            'icon' => 'required',
            'name' => 'required|string|unique:scenes,name,NULL,id,room_id,' . $this->room_id . '|max:20',
            'room_id' => 'required|exists:rooms,id',
            'commands' => 'required',
            'commands.*.id' => 'required',
            'commands.*.command' => 'required',
            'commands.*.type' => 'required',
            'scheduled' => 'required',
            'scheduleType' => 'required',
            'days'   => 'nullable|required_if:scheduleType,custom|array',
            'days.*' => 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
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
     * <Layer number> (4.0)
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
