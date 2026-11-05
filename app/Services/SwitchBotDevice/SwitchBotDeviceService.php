<?php

namespace App\Services\SwitchBotDevice;

use App\Exports\BaseExport;
use App\Http\Requests\SwitchBotDevice\StoreRequest;
use App\Models\SwitchBotDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwitchBotDeviceService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getPaginatedDevices       (1.0) Get paginated devices with filters,
    //                                 sorting and relations.
    // storeDevices              (2.0) Store devices (create, update, delete)
    //                                 as per the scanned data.
    // exportDevice              (3.0) Export all existing SwitchBot devices
    //                                 into an Excel file.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getPaginatedDevices
     * <Function>Get paginated devices with filters, sorting and relations.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getDevices(Request $request): LengthAwarePaginator
    {
        $sortField = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'asc');

        $relationSorts = [
            'room_name' => 'room',
            'type_name' => 'type',
            'room_area' => 'roomArea as room_area'
        ];

        return SwitchBotDevice::query()
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
                        ->orWhereRelation('type', 'name', 'LIKE', "%{$search}%")
                )
            )
            ->with('type', 'room', 'roomArea', 'latestData')
            ->paginate(15)
            ->through(
                fn($device) => [
                    'id' => $device->id,
                    'name' => $device->name,
                    'serial_number' => $device->serial_number,
                    'context' => $device->context,
                    'enable_data_storage' => (bool) $device->enable_data_storage,
                    'room' => [
                        'id' => $device->room?->id,
                        'name' => $device->room?->name
                    ],
                    'room_area' => [
                        'id' => $device->roomArea?->id,
                        'name' => $device->roomArea?->name,
                        'room_id' => $device->roomArea?->room_id
                    ],
                    'type' => ['name' => $device->type->name],
                    'latest_data' => $device->latestData?->context ?? []
                ]
            );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> storeDevices
     * <Function>Store devices (create, update, delete) as per the scanned data.
     *
     * @param \App\Http\Requests\SwitchBotDevice\StoreRequest $request
     * @return void
     */
    public function storeDevices(StoreRequest $request): void
    {
        DB::transaction(function () use ($request) {
            foreach ($request->devicesForCreation() as $array) {
                SwitchBotDevice::create($array);
            }
            foreach ($request->devicesForDeletion() as $array) {
                $device = SwitchBotDevice::find($array['id']);
                if ($device) {
                    $device->delete();
                }
            }
            foreach ($request->devicesForUpdate() as $array) {
                $device = SwitchBotDevice::firstWhere('serial_number', $array['serial_number']);
                if ($device) {
                    $device->update($array);
                }
            }
        });
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> exportAccount
     * <Function> Export all existing SwitchBot devices into an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportDevices()
    {
        $rows = SwitchBotDevice::with(['room', 'roomArea', 'type'])->get()->map(fn($g) => [
            'room_name'         => $g->room?->name,
            'room_area_name'    => $g->roomArea?->name,
            'device_name'       => $g->name,
            'device_type'       => $g->type->name,
            'serial_number'     => $g->serial_number,
            'created_at'        => $g->created_at,
        ]);

        $headings = ['Room Name', 'Room Area Name', 'Device Name', 'Device Type', 'Serial Number', 'Created At'];

        $export = new BaseExport($rows, $headings);

        return $export->download('switchbot_devices', auth()->user());
    }
}
