<?php

namespace App\Http\Requests\LorawanDevice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class SettingsRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize      (1.0) Determine if the user is authorized to make this
    //                      request.
    // rules          (2.0) Get the validation rules that apply to the request.
    // attributes     (3.0) Get custom attributes for validator errors.
    // withValidator  (4.0) Added a validator for checking.
    // computed       (5.0) Get validated settings from request.

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
            'enable_data_storage' => 'required|boolean',
            'settings'     => 'nullable|array',
            'settings.*'   => 'required|string',
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
            'settings.*' => 'configuration', // general display name
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> withValidator
     * <Function> Added a validator for checking.
     *
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $settings = Arr::from($this->get('settings'));

            if (
                $this->device->model == 'VS121' &&
                !in_array(@$settings['operation'], ['region_people_counter', 'line_crossing_counter'])
            ) {
                $validator->errors()->add('settings.operation', __('validation.custom.invalid_operation'));
            }
        });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> computed
     * <Function> Get validated settings from request.
     *
     * @return array|null
     */
    public function computed(): array|null
    {
        $settings = Arr::from($this->get('settings'));

        return !empty($settings) ? $settings : null;
    }
}
