<?php

namespace App\Imports;

use App\Models\Room;
use App\Models\RoomArea;

class RoomsImport extends BaseImport
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // headers                      (1.0) Define expected CSV column headers
    // model                        (2.0) Return the model to be imported
    // rules                        (3.0) Get the validation rules that apply
    //                                    to the import.
    // customValidationMessages     (4.0) Get the custom validation messages
    //                                    for the import.
    // createRoomAreasForRoom       (5.0) Create room areas for a room

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $headers
     * <Function> Define expected CSV column headers.
     *
     * @var array
     */
    public array $headers = ['number', 'name', 'floor_name', 'room_area_name', 'date_time'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> model
     * <Function> Return the model to be imported.
     *
     * @param array $row
     * @return \App\Models\Room
     */
    public function model(array $row): Room
    {
        $this->rowsAffected++;

        $floor = $this->service->findFloor($row);

        $room = Room::create([
            'number'     => $row['number'],
            'name'       => $row['name'],
            'floor_id'   => $floor?->id,
            'created_at' => date('Y-m-d H:i', strtotime($row['date_time'])),
        ]);

        if (!empty($row['room_area_name'])) {
            $areas = json_decode($row['room_area_name'], true) ?? [];
            $this->createRoomAreasForRoom($room->id, $areas);
        }

        return $room;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the import
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'number'         => 'required|unique:rooms,number|max:4',
            'name'           => 'required|unique:rooms,name|max:20',
            'floor_name'     => 'required|string|exists:floors,name',
            'room_area_name' => 'sometimes|string',
            'date_time'      => 'required|date_format:"Y-m-d H:i"',
        ];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> customValidationMessages
     * <Function> Get the custom validation messages for the import.
     *
     * @return array
     */
    public function customValidationMessages(): array
    {
        return [
            'date_time.date_format' => 'Date time must be in the format YYYY-MM-DD HH:MM (e.g. 2025-09-11 14:30).',
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> createRoomAreasForRoom
     * <Function> Create room areas for a room from an array of room area names.
     *
     * @param string $roomId
     * @param array $roomAreaNames
     * @return void
     */
    private function createRoomAreasForRoom(string $roomId, array $roomAreaNames): void
    {
        foreach ($roomAreaNames as $name) {
            RoomArea::create(['name' => $name, 'room_id' => $roomId]);
        }
    }
}
