<?php

namespace App\Services\SwitchBotRemote;

use App\Exports\BaseExport;
use App\Models\Room;
use App\Models\RoomArea;
use App\Models\SwitchBotRemote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SwitchBotRemoteService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getRemotes                (1.0) Retrieve roles with optional filtering,
    //                                 sorting, and pagination.
    // getDependencies           (2.0) Retrieve all dependencies sorted by
    //                                 latest created.
    // storeRemotes              (3.0) Store all the remotes.
    // initializeRemoteDefaults  (4.0) Initialize default data for remotes
    // getExport                 (5.0)  Handles exporting all remotes.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getRemotes
     * <Function> Retrieve remotes with filtering by search term, sortable by
     *            column, and paginated result.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRemotes(Request $request): LengthAwarePaginator
    {
        $sortField = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'asc');

        $relationSorts = [
            'room_name' => ['relation' => 'room as room_name', 'column' => 'rooms.name'],
            'room_area' => ['relation' => 'roomArea as room_area', 'column' => 'room_areas.name'],
        ];

        return SwitchBotRemote::query()
            ->when(
                isset($relationSorts[$sortField]),
                fn($q) => $q->withAggregate(
                    $relationSorts[$sortField]['relation'],
                    $relationSorts[$sortField]['column']
                )
                    ->orderBy($sortField,  $sortDirection),
                fn($q) => $q->orderBy('name',  $sortDirection)
            )
            ->inRoom($request->query('room'))
            ->when(
                $request->query('search'),
                fn($q, $search) =>
                $q->where(
                    fn($q) => $q
                        ->whereAny(
                            ['serial_number', 'name'],
                            'LIKE',
                            "%{$search}%"
                        )
                        ->orWhereRelation('room', 'rooms.name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('roomArea', 'room_areas.name', 'LIKE', "%{$search}%")
                )
            )
            ->with('type', 'room', 'roomArea', 'latestData')
            ->paginate(15)
            ->through(
                fn($remote) => [
                    'id' => $remote->id,
                    'name' => $remote->name,
                    'serial_number' => $remote->serial_number,
                    'context' => $remote->context,
                    'enable_data_storage' => (bool) $remote->enable_data_storage,
                    'room' => [
                        'id' => $remote->room?->id,
                        'name' => $remote->room?->name
                    ],
                    'room_area' => [
                        'id' => $remote->roomArea?->id,
                        'name' => $remote->roomArea?->name,
                        'room_id' => $remote->roomArea?->room_id
                    ],
                    'latest_data' => $remote->latestData?->context ?? []
                ]
            );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getDependencies
     * <Function> Retrieve all dependencies sorted by latest created.
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'rooms' => Room::all(),
            'roomAreas' => RoomArea::all(),
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> storeRemotes
     * <Function> Store all the remotes.
     *
     * @param array $remotesForCreation
     * @param array $remotesForDeletion
     * @param array $remotesForUpdate
     * @return void
     */
    public function storeRemotes(array $remotesForCreation, array $remotesForDeletion, array $remotesForUpdate): void
    {
        DB::transaction(function () use ($remotesForCreation, $remotesForDeletion, $remotesForUpdate) {
            // Process new remotes (creation)
            foreach ($remotesForCreation as $array) {
                SwitchBotRemote::create($array);
            }

            // Process outdated remotes (deletion)
            foreach ($remotesForDeletion as $array) {
                $remote = SwitchBotRemote::find($array['id']);
                $remote->delete();
            }

            foreach ($remotesForUpdate as $data) {
                SwitchBotRemote::where('serial_number', $data['serial_number'])->update($data);
            }

            $this->initializeRemoteDefaults([
                ['type' => 'Air Conditioner', 'config' => 'sb_ac_default'],
                ['type' => 'Others', 'name' => ['%tv%', '%television%'], 'config' => 'sb_tv_default'],
                ['type' => 'Others', 'name' => ['%projector%'], 'config' => 'sb_projector_default'],
            ]);
        });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> initializeRemoteDefaults
     * <Function> Initialize default data for remotes without latestData.
     *
     * @param array $types
     * @return void
     */
    private function initializeRemoteDefaults(array $types): void
    {
        foreach ($types as $type) {
            $query = SwitchBotRemote::ofType($type['type'])->doesntHave('latestData');

            if (isset($type['name'])) {
                $query->where(fn($q) => collect($type['name'])->each(fn($n) => $q->orWhere('name', 'LIKE', $n)));
            }

            $query->get()->each(fn($remote) => $remote->latestData()->updateOrCreate(
                ['device_type' => get_class($remote), 'device_id' => $remote->id],
                ['context' => config("settings.{$type['config']}")]
            ));
        }
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getExport
     * <Function> Handles exporting all remotes.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getExport(): BinaryFileResponse
    {
        $rows = SwitchBotRemote::with(['hub.room', 'hub.roomArea'])->get()->map(fn($remote) => [
            'Room Name'     => $remote->hub?->room?->name,
            'Room Area'     => $remote->hub?->roomArea?->name,
            'Device Name'   => $remote->name,
            'Serial Number' => $remote->serial_number,
            'Date Created'  => $remote->created_at?->timezone('Asia/Manila')->toDateTimeString(),
        ]);

        $headings = ['Room Name', 'Room Area', 'Device Name', 'Serial Number', 'Date Created',];

        $export = new BaseExport($rows, $headings);

        return $export->download('switchbot_remotes', auth()->user());
    }
}
