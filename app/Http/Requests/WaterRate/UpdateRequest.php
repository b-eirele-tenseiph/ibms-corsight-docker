<?php

namespace App\Http\Requests\WaterRate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize            (1.0) Determine if the user is authorized to make
    //                            this request.
    // rules                (2.0) Get the validation rules that apply to the
    //                            request.
    // withValidator        (3.0) Prepare the data for validation.
    // messages             (4.0) Get the custom validation messages for the
    //                            request.

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
            'consumer_type' => 'sometimes|string|in:residential,commercial,industrial',
            'meter_size' => 'sometimes|string|max:50',
            'effective_date' => 'sometimes|date',
            'notes' => 'nullable|string',
            'tiers' => 'sometimes|array|min:1',
            'tiers.*.min_cubic_meters' => 'required_without:tiers|numeric|min:0',
            'tiers.*.max_cubic_meters' => 'nullable|numeric|gt:tiers.*.min_cubic_meters',
            'tiers.*.rate_per_cubic_meter' => 'required_without:tiers|numeric|min:0',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> withValidator
     * <Function> Prepare the data for validation.
     *
     * @return void
     */
    protected function withValidator($validator): void
    {
        $validator->after(
            fn($validator) =>
            collect($validator->errors()->keys())
                ->filter(fn($key) => str_starts_with($key, 'tiers.'))
                ->skip(1)
                ->each(fn($key) => $validator->errors()->forget($key))
        );
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> messages
     * <Function> Get the custom validation messages for the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'tiers.required' => __('validation.custom.water_tier_required'),

            'tiers.*.min_cubic_meters.required' => __('validation.custom.water_tier_min_cubic_meters_required'),
            'tiers.*.min_cubic_meters.numeric' => __('validation.custom.water_tier_min_cubic_meters_numeric'),

            'tiers.*.max_cubic_meters.numeric' => __('validation.custom.water_tier_max_cubic_meters_numeric'),
            'tiers.*.max_cubic_meters.gt' => __('validation.custom.water_tier_max_cubic_meters_gt'),

            'tiers.*.rate_per_cubic_meter.required' => __('validation.custom.water_tier_rate_per_cubic_meter_required'),
            'tiers.*.rate_per_cubic_meter.numeric' => __('validation.custom.water_tier_rate_per_cubic_meter_numeric'),
        ];
    }
}
