<?php

namespace App\Http\Requests\Room;

use App\Models\Floor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize        (1.0) Determine if the user is authorized to make this
    //                        request.
    // rules            (2.0) Get the validation rules that apply to the
    //                        request.
    // attributes       (3.0) Get custom attributes for validator errors.
    // roomUpdateData   (4.0) Transform validated input data for update 
    //                        processing.
    // roomAreas        (5.0) Extract filtered room area input values.

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
        $room = $this->route('room');
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'name')->ignore($room?->id),
            ],
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rooms', 'number')->ignore($room->id),
            ],
            'floor_name' => ['nullable', 'max:20'],
            'room_area' => ['nullable', 'array'],
            'external_apps' => ['nullable', 'array'],
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
            'name' => 'Room Name',
            'number' => 'Room Number',
            'floor_name' => 'Floor Name',
            'room_area' => 'Room Area(s)',
            'external_apps' => 'External Applications',
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> roomUpdateData
     * <Function> Transform validated input data for update processing.
     *
     * @return array
     */
    public function roomUpdateData(): array
    {
        $data = $this->validated();

        $floorId = null;

        if (!empty($data['floor_name'])) {
            $floor = Floor::firstOrCreate(['name' => $data['floor_name']]);
            $floorId = $floor->id;
        }

        return [
            'name' => $data['name'],
            'number' => $data['number'],
            'floor_id' => $floorId,
            'external_apps' => $data['external_apps'] ?? [],
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> roomAreas
     * <Function> Extract filtered room area input values.
     *
     * @return array
     */
    public function roomAreas(): array
    {
        return array_filter($this->input('room_area', [])); // filters out empty strings
    }
}
