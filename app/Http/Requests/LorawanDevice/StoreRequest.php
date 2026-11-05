<?php

namespace App\Http\Requests\LorawanDevice;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize    (1.0) Determine if the user is authorized to make this
    //                    request.
    // rules        (2.0) Get the validation rules that apply to the request.
    // computed     (3.0) Get validated data and attach online depending if
    //                    room is set.
    // attributes   (4.0) Get custom attributes for validator errors.

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
            'name' => 'required|string',
            'dev_eui' => 'required|max:16|min:16|regex:/^[a-fA-F0-9]+$/u|unique:lorawan_devices,dev_eui',
            'manufacturer_id' => 'required',
            'model' => 'required',
            'gateway_id' => 'required',
            'room_area_id' => 'nullable|exists:room_areas,id',
            'room_id' => 'required',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> computed
     * <Function> Get validated data and attach online depending if room is set.
     *
     * @return array
     */
    public function computed(): array
    {
        // Parse validated form
        $validated = $this->validated();

        // Attached default settings if supported
        switch ($validated['model']) {
            case 'WS501': // Milesight AI Workplace settings
                $validated['context'] = config('settings.switch1');
                break;
            case 'WS502': // Milesight AI Workplace settings
                $validated['context'] = config('settings.switch2');
                break;
            case 'WS503': // Milesight AI Workplace settings
                $validated['context'] = config('settings.switch3');
                break;
            case 'UC300': // Milesight iot controller settings
                $validated['context'] = config('settings.iot_controller');
                break;
            case 'VS121': // Milesight AI Workplace settings
                $validated['context'] = config('settings.ai_sensor');
                break;
            case 'ADW300': // Acrel Electric meter settings
                $validated['context'] = config('settings.electric_meter');
                break;
            default:
                break;
        }

        // Return modified validated form
        return $validated;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> attributes
     * <Function> Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'Device Name',
            'dev_eui' => 'Device EUI',
            'manufacturer_id' => 'Manufacturer',
            'model' => 'Model',
            'gateway_id' => 'Gateway IP address',
            'room_id' => 'Room',
            'room_area_id' => 'Room Area',
        ];
    }
}
