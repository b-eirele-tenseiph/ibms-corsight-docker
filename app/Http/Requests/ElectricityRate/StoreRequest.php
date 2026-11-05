<?php

namespace App\Http\Requests\ElectricityRate;

use App\Models\ElectricityRate;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize            (1.0) Determine if the user is authorized to make
    //                            this request.
    // rules                (2.0) Get the validation rules that apply to the
    //                            request.
    // prepareForValidation (3.0) Prepare the data for validation.

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
            'rate_per_kwh' => 'required|numeric',
            'effective_date' => 'required|date|unique:electricity_rates,effective_date',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> prepareForValidation
     * <Function> Prepare the data for validation.
     *
     * @return array
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'effective_date' => Carbon::parse($this->input('effective_date'))->format('Y-m-d'),
        ]);
    }
}
