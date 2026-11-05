<?php

namespace App\Services\SwitchBotHub;

use App\Http\Requests\SwitchBotHub\StoreRequest;
use App\Models\SwitchBotHub;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\BaseExport;

class SwitchBotHubService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getSwitchBotHubs                  (1.0) Fetch switchbot hubs
    // storeHubs                         (2.0) Store scanned hubs
    // exportHubs                        (3.0) Export all existing SwitchBot
    //                                         Hubs into an Excel file.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getSwitchBotHubs
     * <Function> Fetch switchbot hubs
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSwitchBotHubs(Request $request): LengthAwarePaginator
    {
        $sortField = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'asc');

        $relationSorts = [
            'room_name' => 'room',
            'room_area' => 'roomArea as room_area'
        ];

        return SwitchBotHub::query()
            ->when(
                isset($relationSorts[$sortField]),
                fn($q) => $q->withAggregate(
                    $relationSorts[$sortField],
                    'name'
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
                        ->whereAny(['serial_number', 'name'], 'LIKE', "%{$search}%")
                        ->orWhereRelation('room', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('roomArea', 'name', 'LIKE', "%{$search}%")
                )
            )
            ->with('room', 'roomArea')
            ->paginate(15)
            ->through(
                fn($hub) => [
                    'id' => $hub->id,
                    'name' => $hub->name,
                    'serial_number' => $hub->serial_number,
                    'room' => [
                        'id' => $hub->room?->id,
                        'name' => $hub->room?->name
                    ],
                    'room_area' => [
                        'id' => $hub->roomArea?->id,
                        'name' => $hub->roomArea?->name,
                        'room_id' => $hub->roomArea?->room_id
                    ]
                ]
            );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> storeHubs
     * <Function> Store scanned hubs
     *
     * @param \App\Http\Requests\SwitchBotHub\StoreRequest $request
     * @return void
     */
    public function storeHubs(StoreRequest $request): void
    {
        DB::transaction(function () use ($request) {
            foreach ($request->hubsForCreation() as $array) {
                SwitchBotHub::create($array);
            }
            foreach ($request->hubsForDeletion() as $array) {
                $hub = SwitchBotHub::find($array['id']);
                if ($hub) {
                    $hub->delete();
                }
            }
            foreach ($request->hubsForUpdate() as $array) {
                $hub = SwitchBotHub::firstWhere('serial_number', $array['serial_number']);
                if ($hub) {
                    unset($array['name']); // Exclude the "name" field
                    $hub->update($array);
                }
            }
        });
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> exportAccount
     * <Function> Export all existing SwitchBot Hubs into an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportHubs(): mixed
    {
        $rows = SwitchBotHub::with(['room', 'roomArea', 'type'])->get()->map(fn($g) => [
            'room_name'         => $g->room?->name,
            'room_area_name'    => $g->roomArea?->name,
            'hub_name'          => $g->name,
            'serial_number'     => $g->serial_number,
            'created_at'        => $g->created_at,
        ]);

        $headings = ['Room Name', 'Room Area Name', 'Hub Name', 'Serial Number', 'Created At'];

        $export = new BaseExport($rows, $headings);

        return $export->download('switchbot_hubs', auth()->user());
    }
}
