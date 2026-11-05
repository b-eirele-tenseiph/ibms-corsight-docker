<?php

namespace App\Imports;

use App\Models\Floor;
use App\Models\LorawanGateway;
use App\Models\Manufacturer;
use App\Models\Room;
use App\Models\RoomArea;
use App\Models\SwitchBotAccount;

class RowModelMapper
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // findRoom          (1.0) Retrieve Room using room_name column upon import.
    // findGateway       (2.0) Retrieve Lorawan Gateway using gateway_ip column
    //                         upon import.
    // findRoomArea      (3.0) Retrieve Room Area using room_area_name column
    //                         upon import. Room area should be base on areas
    //                         of the retrieved Room.
    // findManufacturer  (4.0) Retrieve Manufacturer using dev_eui column upon
    //                         import.
    // findAccountUsing  (5.0) Retrieve Account using room_name column upon
    // RoomName                import.
    // findFloor         (6.0) Finds a floor by its ID from the given row.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> findRoom
     * <Function> Retrieve Room using room_name column upon import.
     *
     * @param array $row
     * @return \App\Models\Room|null
     */
    public function findRoom(array $row): ?Room
    {
        return Room::where('name', $row['room_name'])->first();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> findGateway
     * <Function> Retrieve Lorawan Gateway using gateway_ip column upon import.
     *
     * @param array $row
     * @return \App\Models\LorawanGateway
     */
    public function findGateway(array $row): LorawanGateway
    {
        return LorawanGateway::where('ip_address', $row['gateway_ip'])->first();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> findRoomArea
     * <Function> Retrieve Room Area using room_area_name column upon import.
     *            Room area should be base on areas of the retrieved Room.
     *
     * @param array $row
     * @param \App\Models\Room $room
     * @return \App\Models\RoomArea|null
     */
    public function findRoomArea(array $row, ?Room $room): ?RoomArea
    {
        if (is_null($room)) return null;

        return $room->roomAreas()->where('name', $row['room_area_name'])->first();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> findManufacturer
     * <Function> Retrieve Manufacturer using dev_eui column upon import.
     *
     * @param array $row
     * @return \App\Models\Manufacturer
     */
    public function findManufacturer(array $row): Manufacturer
    {
        return Manufacturer::all()
            ->first(
                fn($man) =>
                preg_match($man->prefix_regex, $row['dev_eui'])
            );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> findAccountUsingRoomName
     * <Function> Retrieve Account using room_name column upon import.
     *
     * @param array $row
     * @return \App\Models\SwitchBotAccount|null
     */
    public function findAccountUsingRoomName(array $row): ?SwitchBotAccount
    {
        return SwitchBotAccount::whereRelation('room', 'name', $row['room_name'])->first();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> findFloor
     * <Function> Retrieve Floor using floor_name column upon import.
     *
     * @param array
     * @return \App\Models\Floor|null
     */
    public function findFloor(array $row): Floor
    {
        return Floor::where('name', $row['floor_name'])->first();
    }
}
