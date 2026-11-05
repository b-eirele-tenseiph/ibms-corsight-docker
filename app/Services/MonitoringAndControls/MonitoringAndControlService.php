<?php

namespace App\Services\MonitoringAndControls;

use App\Models\LorawanDevice;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotRemote;
use Illuminate\Pagination\LengthAwarePaginator;

class MonitoringAndControlService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getSwitches      (1.0) Retrieve paginated switch devices with filters.
    // getAircon        (2.0) Retrieve paginated aircon devices with filters.
    // getPlugs         (3.0) Retrieve paginated plug devices with filters.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getSwitches
     *  <Function> Retrieve paginated switch devices with filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getSwitches(array $filters): LengthAwarePaginator
    {
        $relationSorts = [
            'room_name' => 'room',
            'room_area' => 'roomArea as area',
        ];

        return LorawanDevice::deviceType('switches')
            ->with(['room', 'roomArea', 'latestData'])
            ->when(
                $filters['room_id'] ?? null,
                fn($q, $roomId) => $q->where('room_id', $roomId)
            )
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) =>
                $q->where(
                    fn($subQ) => $subQ
                        ->whereAny(['name', 'model'], 'LIKE', "%{$search}%")
                        ->orWhereRelation('room', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('roomArea', 'name', 'LIKE', "%{$search}%")
                )
            )
            ->when(
                isset($relationSorts[$filters['sort']]),
                fn($q) => $q->withAggregate($relationSorts[$filters['sort']], 'name')
                    ->orderBy($filters['sort'], $filters['direction']),
                fn($q) => $q->orderBy('name', $filters['direction'])
            )
            ->paginate(10)
            ->withQueryString()
            ->through(fn($device) => [
                'id' => $device->id,
                'name' => $device->name,
                'model' => $device->model,
                'context' => $device->context,
                'room' => $device->room?->only('name'),
                'room_area' => $device->roomArea?->only('name'),
                'latest_data' => collect($device->latestData?->context ?? [])
                    ->filter(fn($v, $key) => str_starts_with($key, 'switch_'))
                    ->all(),
            ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getAircon
     * <Function> Retrieve paginated aircon devices with filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAircon(array $filters): LengthAwarePaginator
    {
        $page = request('page', 1);

        $relationSorts = [
            'room_name' => ['relation' => 'room as room_name', 'column' => 'rooms.name'],
            'room_area' => ['relation' => 'roomArea as room_area', 'column' => 'room_areas.name'],
        ];

        $data = collect([
            LorawanDevice::deviceType('thermostat')
                ->when(
                    $filters['room_id'] ?? null,
                    fn($query, $roomId) => $query->where('room_id', $roomId)
                ),
            SwitchBotRemote::ofType('Air Conditioner')
                ->when(
                    $filters['room_id'] ?? null,
                    fn($query, $roomId) => $query->whereHas('hub', fn($q) => $q->where('room_id', $roomId))
                )
        ])
            ->flatMap(
                fn($q) => $q->with(['room', 'roomArea', 'latestData'])
                    ->when(
                        $filters['search'] ?? null,
                        function ($query, $search) {
                            $tableName = $query->getModel()->getTable();
                            return $query->where(function ($q) use ($search, $tableName) {
                                $q->where("$tableName.name", 'LIKE', "%$search%")
                                    ->orWhereHas('room', fn($subQ) => $subQ->where('rooms.name', 'LIKE', "%$search%"))
                                    ->orWhereHas('roomArea', fn($subQ) => $subQ->where('room_areas.name', 'LIKE', "%$search%"));
                            });
                        }
                    )
                    ->when(
                        isset($relationSorts[$filters['sort']]),
                        fn($q) => $q->withAggregate(
                            $relationSorts[$filters['sort']]['relation'],
                            $relationSorts[$filters['sort']]['column']
                        )
                            ->orderBy($filters['sort'], $filters['direction']),
                        fn($q) => $q->orderBy('name', $filters['direction'])
                    )
                    ->get()
            )
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'model' => $d->model ?? $d->type?->name,
                'room' => $d->room?->only('name'),
                'room_area' => $d->roomArea?->only('name'),
                'latest_data' => $d->latestData?->context,
            ]);

        return new LengthAwarePaginator(
            $data->forPage($page, 10)->values(),
            $data->count(),
            10,
            $page,
            ['path' => request()->url()]
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getPlugs
     * <Function> Retrieve paginated plug devices with filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPlugs(array $filters): LengthAwarePaginator
    {
        $relationSorts = [
            'room_name' => 'room',
            'room_area' => 'roomArea as area',
        ];

        return SwitchBotDevice::ofType('Plug', 'Plug Mini (JP)', 'Plug Mini (US)')
            ->with(['room', 'roomArea', 'type', 'latestData'])
            ->when(
                $filters['room_id'] ?? null,
                fn($q, $roomId) => $q->where('room_id', $roomId)
            )
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) =>
                $q->where(
                    fn($subQ) => $subQ
                        ->where('name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('room', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('roomArea', 'name', 'LIKE', "%{$search}%")
                )
            )
            ->when(
                isset($relationSorts[$filters['sort']]),
                fn($q) => $q->withAggregate($relationSorts[$filters['sort']], 'name')
                    ->orderBy($filters['sort'], $filters['direction']),
                fn($q) => $q->orderBy('name', $filters['direction'])
            )
            ->paginate(10)
            ->withQueryString()
            ->through(fn($device) => [
                'id' => $device->id,
                'name' => $device->name,
                'model' => $device->model,
                'type' => $device->type?->only('name'),
                'room' => $device->room?->only('name'),
                'room_area' => $device->roomArea?->only('name'),
                'latest_data' => $device->latestData?->context
            ]);
    }
}
