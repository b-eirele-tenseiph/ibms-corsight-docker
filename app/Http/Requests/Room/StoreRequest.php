<?php

namespace App\Http\Requests\Room;

use App\Models\Floor;
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
    // roomData     (4.0) Transform validated input data for storage processing.

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
            'name' => 'required|string|max:20|unique:rooms,name',
            'number' => 'required|string|max:4|unique:rooms,number',
            'floor_name' => 'nullable|max:20',
            'room_area' => 'nullable|array',
            'external_apps' => 'nullable|array'
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
            'room_area' => 'Room Area',
            'external_apps' => 'External Applications'
        ];
    }


    /**
     * <Layer number> (4.0)
     *
     * <Processing name> roomData
     * <Function> Transform validated input data for storage processing.
     *
     * @return array
     */
    public function roomData(): array
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
}
