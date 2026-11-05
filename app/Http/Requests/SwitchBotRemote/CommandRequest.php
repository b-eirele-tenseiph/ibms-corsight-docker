<?php

namespace App\Http\Requests\SwitchBotRemote;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CommandRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize                      (1.0) Determine if the user is authorized
    //                                      to make this request.
    // rules                          (2.0) Get the validation rules that apply
    //                                      to the request.
    // payload                        (3.0) Generate the payload for the
    //                                      SwitchBot remote command.
    // newData                        (4.0) Generate new data based on the
    //                                      received parameter string.
    // processTelevisionData          (5.0) Process television data based on
    //                                      the command input.
    // processProjectorData           (6.0) Process projector data based on
    //                                      the command input.
    // processAirConditionerData      (7.0) Process air conditioner data and
    //                                      update based on the command input.

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
     * <Function> Define the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'command' => 'required|string',
            'parameter' => 'required',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> payload
     * <Function> Generate the payload for the SwitchBot remote command.
     *
     * @return array
     */
    public function payload(): array
    {
        return match ($this->remote->classification) {
            'television', 'projector' => [
                'command' => $this->command,
                'parameter' => $this->parameter,
                'commandType' => 'customize',
            ],
            'aircon' => [
                'commandType' => 'command',
                'command' => 'setAll',
                'parameter' => implode(',', $this->newData()),
            ],
            default => throw new \InvalidArgumentException("Unsupported remote type: {$this->remote->classification}"),
        };
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> newData
     * <Function> Generate new data based on the received parameter string.
     *
     * @return array
     */
    public function newData(): array
    {
        $data = $this->remote->latestData->context ?? [];
        $type = $this->remote->classification;

        return match ($type) {
            'television' => $this->processTelevisionData($data),
            'projector'  => $this->processProjectorData($data),
            'aircon'     => $this->processAirConditionerData($data),
            default      => $data,
        };
    }


    /**
     * <Layer number> (5.0)
     *
     * <Processing name> processTelevisionData
     * <Function> Process television data based on the command input.
     *
     * @param array $data
     * @return array
     */
    protected function processTelevisionData(array $data): array
    {
        if ($this->command === 'POWER') {
            $data['powerState'] = Str::lower($data['powerState']) === 'off' ? 'on' : 'off';
        }

        return $data;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> processProjectorData
     * <Function> Process projector data based on the command input.
     *
     * @param array $data
     * @return array
     */
    protected function processProjectorData(array $data): array
    {
        if (in_array($this->command, ['ON', 'OFF'])) {
            $data['powerState'] = $this->command === 'ON' ? 'on' : 'off';
        }

        return $data;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> processAirConditionerData
     * <Function> Process air conditioner data and update based on the command input.
     *
     * @param array $data
     * @return array
     */
    protected function processAirConditionerData(array $data): array
    {
        $defaultConfig = config('settings.sb_ac_default');
        $data = array_merge($defaultConfig, $data);
        $data[$this->command] = $this->parameter;

        return $data;
    }
}
