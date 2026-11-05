<?php

namespace App\Services\RoomMonitoring;

use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class RoomMonitoringService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $room                 (1.0)  The Room model instance.
    // $area                 (2.0)  The name of the room area.
    // roomsQuery            (3.0)  Creates a base query for rooms.
    // getRoomsQuery         (4.0)  Gets a query for all rooms on a specific
    //                              floor.
    // getFilteredRoomsQuery (5.0)  Gets a query for rooms filtered by floor
    //                              and a specific room slug.
    // forRoom               (6.0)  Sets the Room instance for the current
    //                              object.
    // inArea                (7.0)  Sets the area for the current object based
    //                              on a room area ID.
    // getRoomSwitches       (8.0)  Retrieves Lorawan switches for the current
    //                              room and area.
    // getRoomThermostat     (9.0)  Retrieves Lorawan thermostats for the
    //                              current room and area.
    // getRoomDevices        (10.0) Retrieves SwitchBot devices for the current
    //                              room and area.
    // getRoomCurtains       (11.0) Retrieves SwitchBot curtain devices for the
    //                              current room and area.
    // getRoomBlindTilt      (12.0) Retrieves SwitchBot blind tilt devices for
    //                              the current room and area.
    // getRoomAircon         (13.0) Retrieves SwitchBot air conditioner remotes
    //                              for the current room and area.
    // getRoomProjector      (14.0) Retrieves SwitchBot projector remotes for
    //                              the current room and area.
    // getRoomTelevision     (15.0) Retrieves SwitchBot television remotes for
    //                              the current room and area.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $room
     * <Function> The Room model instance
     *
     * @var Room|null
     */
    protected ?Room $room = null;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $area
     * <Function> The name of the room area.
     *
     * @var string|null
     */
    protected ?string $area = null;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> roomsQuery
     * <Function> Creates a base query for rooms.
     *
     * @param string|null $floor
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function roomsQuery(?string $floor): Builder
    {
        return Room::with('roomAreas:id,room_id,name,has_active_device')
            ->select('id', 'name', 'number', 'device_status')
            ->OnFloor($floor)
            ->orderBy('number');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getRoomsQuery
     * <Function> Gets a query for all rooms on a specific floor.
     *
     * @param string|null $floor
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRoomsQuery(?string $floor): Builder
    {
        return $this->roomsQuery($floor);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getFilteredRoomsQuery
     * <Function> Gets a query for rooms filtered by floor and a specific room slug.
     *
     * @param string|null $floor
     * @param string|null $roomSlug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredRoomsQuery(?string $floor, ?string $roomSlug): Builder
    {
        return $this->roomsQuery($floor)
            ->when($roomSlug, fn($q) => $q->where('name', unslug($roomSlug)));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> forRoom
     * <Function> Sets the Room instance for the current object.
     *
     * @param string $room
     * @return static
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function forRoom(string $room): static
    {
        $this->room = Room::where('id', $room)->with('roomAreas')->firstOrFail();

        return $this;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> inArea
     * <Function> Sets the area for the current object based on a room area ID.
     *
     * @param string $area
     * @return static
     */
    public function inArea(string $area): static
    {
        $this->area = $this->room?->roomAreas->where('id', $area)->first()?->name;
        return $this;
    }

    /**
     * <Layer number> (7.5)
     *
     * <Processing name> mapLorawanDevice
     * <Function> Maps a LoRaWAN device model to the standard device output shape.
     *
     * @param \App\Models\LorawanDevice $d
     * @return array
     */
    private function mapLorawanDevice($d): array
    {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'model' => $d->model,
            'context' => $d->context,
            'room' => $d->room->id,
            'room_area' => $d->roomArea?->name ?? '',
            'latest_data' => $d->latestData?->context,
        ];
    }

    /**
     * <Layer number> (7.6)
     *
     * <Processing name> mapSwitchBotDevice
     * <Function> Maps a SwitchBot device/remote model to the standard device output shape.
     *
     * @param \Illuminate\Database\Eloquent\Model $d
     * @return array
     */
    private function mapSwitchBotDevice($d): array
    {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'type' => $d->type->name,
            'context' => $d->context,
            'room' => $d->room->id,
            'room_area' => $d->roomArea?->name ?? '',
            'latest_data' => $d->latestData?->context,
        ];
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getRoomSwitches
     * <Function> Retrieves a collection of Lorawan switches for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomSwitches(): Collection
    {
        return $this->room->lorawanDevices()
            ->deviceType('switches')
            ->inRoomArea($this->area)
            ->with('latestData')
            ->get()
            ->map(fn($d) => $this->mapLorawanDevice($d))
            ->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getRoomThermostat
     * <Function> Retrieves a collection of Lorawan thermostats for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomThermostat(): Collection
    {
        return $this->room->lorawanDevices()
            ->deviceType('thermostat')
            ->inRoomArea($this->area)
            ->with('latestData')
            ->get()
            ->map(fn($d) => $this->mapLorawanDevice($d))
            ->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getRoomDevices
     * <Function> Retrieves a collection of SwitchBot devices for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomDevices(): Collection
    {
        return $this->room->switchBotDevices()
            ->ofType('Plug', 'Plug Mini (JP)', 'Plug Mini (US)', 'Bot', 'Smart Lock', 'Smart Lock Pro')
            ->inRoomArea($this->area)
            ->with('latestData')
            ->get()
            ->map(fn($d) => $this->mapSwitchBotDevice($d))
            ->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getRoomCurtains
     * <Function> Retrieves a collection of SwitchBot curtain devices for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomCurtains(): Collection
    {
        return $this->room->switchBotDevices()
            ->ofType('Curtain')
            ->where('context->master', true)
            ->inRoomArea($this->area)
            ->with('latestData')
            ->get()
            ->map(fn($d) => $this->mapSwitchBotDevice($d))
            ->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> getRoomBlindTilt
     * <Function> Retrieves a collection of SwitchBot blind tilt devices for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomBlindTilt(): Collection
    {
        return $this->room->switchBotDevices()
            ->ofType('Blind Tilt')
            ->where('context->master', true)
            ->inRoomArea($this->area)
            ->with('latestData')
            ->get()
            ->map(fn($d) => $this->mapSwitchBotDevice($d))
            ->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> getRoomAircon
     * <Function> Retrieves a collection of SwitchBot air conditioner remotes for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomAircon(): Collection
    {
        return $this->room
            ->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->ofType('Air Conditioner')
                    ->inRoomArea($this->area)
                    ->with('latestData');
            }])
            ->get()
            ->flatMap->remotes
            ->map(fn($d) => $this->mapSwitchBotDevice($d))
            ->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> getRoomProjector
     * <Function> Retrieves a collection of SwitchBot projector remotes for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomProjector(): Collection
    {
        return $this->room
            ->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->ofType('Others')
                    ->where('name', 'LIKE', '%projector%')
                    ->inRoomArea($this->area)
                    ->with('latestData');
            }])
            ->get()
            ->flatMap->remotes
            ->map(fn($d) => $this->mapSwitchBotDevice($d))
            ->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> getRoomTelevision
     * <Function> Retrieves a collection of SwitchBot television remotes for the current room and area.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomTelevision(): Collection
    {
        return $this->room
            ->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->ofType('Others')
                    ->where(function ($x) {
                        $x->where('name', 'LIKE', '%television%')
                            ->orWhere('name', 'LIKE', '%tv%');
                    })
                    ->inRoomArea($this->area)
                    ->with('latestData');
            }])
            ->get()
            ->flatMap->remotes
            ->map(fn($d) => $this->mapSwitchBotDevice($d))
            ->whereNotNull('latest_data');
    }
}
