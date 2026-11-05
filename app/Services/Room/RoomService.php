<?php

namespace App\Services\Room;

use App\Exports\BaseExport;
use App\Http\Requests\Room\{StoreRequest, UpdateRequest};
use App\Imports\RoomsImport;
use App\Models\{Room, Floor, RoomArea};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RoomService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getIndexData      (1.0) Returns index page data.
    // getRooms          (2.0) Get rooms with filtering, sorting and pagination.
    // createRoom        (3.0) Create a new room with its areas.
    // createRoomAreas   (4.0) Create room areas for a given room.
    // updateRoom        (5.0) Update an existing room and its areas.
    // syncRoomAreas     (6.0) Sync room areas.
    // getTemplate       (7.0) Handles retrieving the template that will be used
    //                         for import.
    // getImport         (8.0) Handles file import with validation and error
    //                         handling
    // getExport         (9.0) Handles exporting all room area.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getIndexData
     * <Function> Returns index page data.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getIndexData(Request $request): array
    {
        return [
            'rooms' => $this->getRooms($request),
            'floors' => Floor::with('rooms')->get(),
            'queryParams' => $request->query(),
            'external_apps' => ['LG', 'ELECTROLUX', 'AXIS', 'TUYA'],
        ];
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getRooms
     * <Function> Get rooms with filtering, sorting and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRooms(Request $request): LengthAwarePaginator
    {
        $filters = $request->only('search');

        $sortField = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'desc');

        $allowedSortFields = ['id', 'name', 'number', 'created_at'];

        return Room::with(['floor', 'roomAreas'])
            ->when($sortField, function ($query) use ($sortField, $sortDirection, $allowedSortFields) {
                if ($sortField == 'floor') {
                    return $query->withAggregate('floor', 'name')
                        ->orderBy('floor_name', $sortDirection);
                }
                if (!in_array($sortField, $allowedSortFields, true)) {
                    $sortField = 'id';
                }
                return $query->orderBy($sortField, $sortDirection);
            })
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) =>
                $q->where(function ($query) use ($search) {
                    $query->whereAny(['rooms.name', 'number'], 'like', "%$search%")
                        ->orWhereHas('floor', fn($q) => $q->where('name', 'like', "%$search%"));
                })
            )
            ->OnFloor($request->query('floor'))
            ->paginate(10)
            ->through(
                fn($room) => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'number' => $room->number,
                    'floor' => $room->floor,
                    'room_area' => $room->roomAreas->map(function ($area) {
                        return [
                            'id'   => $area->id,
                            'name' => $area->name,
                        ];
                    })->toArray(),
                    'external_apps' => $room->external_apps,
                ]
            );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createRoom
     * <Function> Create a new room with its areas.
     *
     * @param \App\Http\Requests\Room\StoreRequest $request
     * @return \App\Models\Room
     */
    public function createRoom(StoreRequest $request): Room
    {
        $room = Room::create($request->roomData());
        $this->createRoomAreas($room->id, $request);

        return $room;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> createRoomAreas
     * <Function> Create room areas for a given room.
     *
     * @param string $roomId
     * @param mixed $request
     * @return void
     */
    protected function createRoomAreas(string $roomId, mixed $request): void
    {
        $data = $request->validated();

        if (!empty($data['room_area'])) {
            foreach ($data['room_area'] as $area) {
                $name = is_array($area) ? ($area['name'] ?? null) : $area;

                if ($name) {
                    RoomArea::create([
                        'room_id' => $roomId,
                        'name' => $name,
                    ]);
                }
            }
        }
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> updateRoom
     * <Function> Update an existing room and its areas.
     *
     * @param \App\Models\Room $room
     * @param \App\Http\Requests\Room\UpdateRequest $request
     * @return \App\Models\Room
     */
    public function updateRoom(Room $room, UpdateRequest $request): Room
    {
        // Update main room info
        $room->update($request->roomUpdateData());

        // Sync room areas
        $this->syncRoomAreas($room->id, $request);

        return $room;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> syncRoomAreas
     * <Function> Sync room areas.
     *
     * @param string $roomId
     * @param mixed $request
     * @return void
     */
    protected function syncRoomAreas(string $roomId, mixed $request): void
    {
        $areas = collect($request->roomAreas());
        $incomingIds = $areas->pluck('id')->filter();

        // delete the room areas that are not in the incoming request
        RoomArea::where('room_id', $roomId)
            ->whereNotIn('id', $incomingIds)
            ->delete();

        // upsert the incoming room areas
        RoomArea::upsert(
            $areas->map(fn($area) => [
                'id'      => $area['id'] ?? Str::uuid(),
                'room_id' => $roomId,
                'name'    => $area['name'],
            ])->toArray(),
            ['id'],
            ['name']
        );
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getTemplate
     * <Function> Handles retrieving the template that will be used for import.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getTemplate(): BinaryFileResponse
    {
        $rows = collect([
            [
                'number'            => '1001',
                'name'              => 'Deluxe Room',
                'floor_name'        => '7th Floor',
                'room_area_name'    => ["Living Room", "Kitchen"],
                'date_time'         => now()->format('Y-m-d H:i'),
            ]
        ]);

        $headings = array_keys($rows->first());

        $export = new BaseExport($rows, $headings);

        return Excel::download($export, $export->getFileName('rooms'));
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getImport
     * <Function> Handles file import with validation and error handling.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', File::types(['xlsx', 'xls', 'csv'])],
        ]);

        $import = new RoomsImport();
        $import->startExecutionTime();

        Excel::import($import, $request->file('file'));

        $import->logImportActivity(
            'Rooms',
            auth()->user(),
            [
                'File Name' => $request->file('file')->getClientOriginalName(),
            ]
        );

        return back()
            ->with(['import' => ['summary' => $import->summary()]]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getExport
     * <Function> Handles exporting all room area.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getExport(): BinaryFileResponse
    {
        $rows = Room::all()->map(fn($room) => [
            'Room Number'   => $room->number,
            'Room Name'     => $room->name,
            'Floor'         => $room->floor->name,
            'Room Areas'    => $room->roomAreas->pluck('name')->join(', '),
            'User'          => $room->roomAccesses->pluck('user.name')->join(', '),
            'External Apps' => collect($room->external_apps)->join(', '),
            'Date Created'  => $room->created_at->timezone('Asia/Manila')->toDateTimeString(),
        ]);

        $headings = array_keys($rows->first());

        $export = new BaseExport($rows, $headings);

        return $export->download('rooms', auth()->user());
    }
}
