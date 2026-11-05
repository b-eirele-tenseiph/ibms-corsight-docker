<?php

namespace App\Services\Corsight;

use App\Http\Requests\CorsightDevice\{StoreRequest, UpdateRequest};
use App\Models\CorsightDevice;
use App\Models\CorsightDeviceData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CorsightDeviceService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getDevices            (1.0) Retrieves a paginated list of devices with
    //                            sorting and filtering options
    // storeDevice           (2.0) Stores a new device based on
    //                            validated data from the request
    // updateDevice          (3.0) Updates an existing device based on
    //                            validated data from the request
    // latestCameraStatuses  (4.0) Retrieves the latest camera_status entry
    //                            per device from the diary.
    // lastActivityByDevice  (5.0) Retrieves the last received event time of
    //                            any kind per device (stale detection).

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getDevices
     * <Function> Retrieves a paginated list of devices with
     *            sorting and filtering options
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getDevices(Request $request): LengthAwarePaginator
    {
        $sortField = $request->query('field', 'created_at');
        $sortDirection = $request->query('direction', 'desc');

        $relationSorts = [
            'room_name' => 'room',
            'room_area' => 'roomArea as room_area',
        ];

        $devices = CorsightDevice::query()
            ->when(
                isset($relationSorts[$sortField]),
                fn($q) => $q->withAggregate($relationSorts[$sortField], 'name')
                    ->orderBy($sortField, $sortDirection),
                fn($q) => $q->orderBy($sortField, $sortDirection)
            )
            ->inRoom($request->query('room'))
            ->when(
                $request->query('search'),
                fn($q, $search) =>
                $q->where(
                    fn($q) => $q
                        ->where('name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('room', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('roomArea', 'name', 'LIKE', "%{$search}%")
                )
            )
            ->with('room', 'roomArea')
            ->paginate(15);

        $deviceIds = collect($devices->items())->pluck('id');
        $latestStatuses = $this->latestCameraStatuses($deviceIds);
        $lastActivity = $this->lastActivityByDevice($deviceIds);
        $staleBefore = now()->subMinutes((int) config('corsight.health_stale_minutes', 30));

        return $devices->through(
            fn($device) => [
                'id' => $device->id,
                'name' => $device->name,
                'role' => $device->role,
                'enable_data_storage' => (bool) $device->enable_data_storage,
                'room' => [
                    'id' => $device->room?->id,
                    'name' => $device->room?->name,
                ],
                'room_area' => [
                    'id' => $device->roomArea?->id,
                    'name' => $device->roomArea?->name,
                    'room_id' => $device->roomArea?->room_id,
                ],
                'health' => [
                    'status' => $latestStatuses->get($device->id)?->context['update_data']['status'] ?? null,
                    'status_str' => $latestStatuses->get($device->id)?->context['update_data']['status_str'] ?? null,
                    'at' => CorsightDisplay::timestamp($latestStatuses->get($device->id)?->received_at),
                    'stale' => Carbon::parse($lastActivity->get($device->id, now()))->lt($staleBefore),
                ],
            ]
        );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> storeDevice
     * <Function> Stores a new device based on validated
     *            data from the request
     *
     * @param \App\Http\Requests\CorsightDevice\StoreRequest $request
     * @return void
     */
    public function storeDevice(StoreRequest $request): void
    {
        CorsightDevice::create($request->validated());
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updateDevice
     * <Function> Updates an existing device based on
     *            validated data from the request
     *
     * @param \App\Http\Requests\CorsightDevice\UpdateRequest $request
     * @param \App\Models\CorsightDevice $device
     * @return void
     */
    public function updateDevice(UpdateRequest $request, CorsightDevice $device): void
    {
        $device->update($request->validated());
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> latestCameraStatuses
     * <Function> Retrieves the latest camera_status diary entry per device
     *            (the camera health shown on the device list).
     *
     * @param \Illuminate\Support\Collection $deviceIds
     * @return \Illuminate\Support\Collection
     */
    private function latestCameraStatuses(Collection $deviceIds): Collection
    {
        if ($deviceIds->isEmpty()) {
            return new Collection();
        }

        return CorsightDeviceData::query()
            ->where('event_type', 'camera_status')
            ->whereIn('device_id', $deviceIds)
            ->orderByDesc('received_at')
            ->get()
            ->groupBy('device_id')
            ->map(fn (Collection $rows) => $rows->first());
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> lastActivityByDevice
     * <Function> Retrieves the last received diary entry time of ANY
     *            event type per device. Freshness is judged on any
     *            event (a camera analyzing people sends appearance
     *            events even between camera_status reports), while the
     *            displayed state still comes from the latest
     *            camera_status entry.
     *
     * @param \Illuminate\Support\Collection $deviceIds
     * @return \Illuminate\Support\Collection
     */
    private function lastActivityByDevice(Collection $deviceIds): Collection
    {
        if ($deviceIds->isEmpty()) {
            return new Collection();
        }

        return CorsightDeviceData::query()
            ->whereIn('device_id', $deviceIds)
            ->groupBy('device_id')
            ->get(['device_id', DB::raw('max(received_at) as last_at')])
            ->pluck('last_at', 'device_id');
    }
}
