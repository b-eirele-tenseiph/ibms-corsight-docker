<?php

namespace App\Http\Requests\DeviceGridPosition;

use App\Models\DeviceGridPosition;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize  (1.0) Determine if the user is authorized to make this request.
    // rules      (2.0) Get the validation rules that apply to the request.
    // computed   (3.0) Get the mapped out collected information.


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
            'positions' => 'required|array',
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> computed
     * <Function> Get the mapped out collected information.
     *
     * @return \Illuminate\Support\Collection
     */
    public function computed()
    {
        return collect($this->validated()['positions'])->map(function ($item) {
            return [
                'needle' => [
                    'device_type' => DeviceGridPosition::CLASSES[$item['type']],
                    'device_id'   => $item['id'],
                    'room_area'   => unslug($item['room_area']),
                    'context->c'  => $item['column'],
                ],
                'content' => [
                    'context' => [
                        'x' => $item['x'],
                        'y' => $item['y'],
                        'w' => $item['w'],
                        'h' => $item['h'],
                        'c' => $item['column'],
                    ]
                ]
            ];
        });
    }
}
