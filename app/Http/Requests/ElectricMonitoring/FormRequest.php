<?php

namespace App\Http\Requests\ElectricMonitoring;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

class FormRequest extends BaseFormRequest
{

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize            (1.0) Determine if the user is authorized to make 
    //                            this request.
    // prepareForValidation (2.0) Prepare the data for validation.
    // rules                (3.0) Get the validation rules that apply to the 
    //                            request.
    // deviceId             (4.0) Get the device id from the request.
    // time                 (5.0) Get the device id from the request.
    // phase                (6.0) Get the device id from the request.

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
     * <Processing name> prepareForValidation
     * <Function> Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $time = $this->input('time');

        if (is_string($time)) {
            $decoded = json_decode($time, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $time = $decoded;
            } elseif (is_numeric($time)) {
                $time = (int) $time;
            }
        }

        $this->merge(['time' => $time]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'device' => ['required'],
            'phase'  => ['sometimes'],
            'time' => [
                'required',
                function ($attr, $value, $fail) {
                    if (!is_int($value) && !is_array($value)) {
                        $fail('time must be integer minutes or an object with start/end');
                    }
                },
            ],
            'time.start'  => ['required_if:time,array', 'date'],
            'time.end'    => ['required_if:time,array', 'date', 'after:time.start'],
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> deviceId
     * <Function> Get the device id from the request.
     *
     * @return string
     */
    public function deviceId(): string
    {
        return (string) $this->input('device');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> time
     * <Function> Get the time from the request.
     *
     * @return int|array
     */
    public function time(): int|array
    {
        return $this->input('time');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> phase
     * <Function> Get the phase from the request.
     *
     * @return string|null
     */
    public function phase(): ?string
    {
        return $this->input('phase');
    }
}
