<?php

namespace App\Services\Lorawan;

use App\Models\LorawanDevice;
use Illuminate\Http\Request;
use App\Exports\BaseExport as Export;
use App\Exports\BaseExport;
use App\Imports\LorawanDevicesImport;
use App\Models\{LorawanGateway, Room, RoomArea};
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LorawanDeviceService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getFilteredDevices      (1.0) Retrieves a paginated list of devices with
    //                               sorting, filtering, and search options.
    // export                  (2.0) Exports devices to Excel.
    // import                  (3.0) Imports devices from Excel.
    // template                (4.0) Retrieves a template for importing devices
    //                               from Excel.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getFilteredDevices
     * <Function> Get filtered lorawan devices.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function getFilteredDevices(Request $request): mixed
    {
        $sortField = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'asc');

        $relationSorts = [
            'room_name' => 'room',
            'room_area' => 'roomArea as room_area',
            'gateway_name' => 'gateway'
        ];

        return LorawanDevice::query()
            ->with(['gateway', 'room', 'roomArea'])
            ->inRoom($request->query('room'))
            ->when(
                isset($relationSorts[$sortField]),
                fn($q) =>
                $q->withAggregate($relationSorts[$sortField], 'name')
            )
            ->when(
                $request->query('search'),
                fn($q, $search) => $q->where(
                    fn($q) => $q
                        ->whereAny(['dev_eui', 'name', 'model'], 'LIKE', "%{$search}%")
                        ->orWhereRelation('room', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('roomArea', 'name', 'LIKE', "%{$search}%")
                        ->orWhereRelation('gateway', 'ip_address', 'LIKE', "%{$search}%")
                )
            )
            ->when($sortField, fn($q) => $q->orderBy($sortField, $sortDirection), fn($q) => $q->latest())
            ->paginate(10);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> export
     * <Function> Exports all Devices to Excel
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): BinaryFileResponse
    {
        $rows = LorawanDevice::get()->map(fn($row) => [
            'Room Name'       => Room::where('id', $row->room_id)->value('name'),
            'Room Area Name'  => RoomArea::where('id', $row->room_area_id)->value('name'),
            'Device'          => $row->name,
            'Dev EUI'         => $row->dev_eui,
            'Model'           => $row->model,
            'Gateway IP'      => LorawanGateway::where('id', $row->gateway->id)->value('ip_address'),
        ]);

        $headings = ['Room Name', 'Room Area Name', 'Device', 'Dev EUI', 'Model', 'Gateway IP'];

        $export = new BaseExport($rows, $headings);

        return $export->download('lorawan_devices', auth()->user());
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> import
     * <Function> Imports Devices from Excel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', File::types(['xlsx', 'xls', 'csv'])],
        ]);

        $import = new LorawanDevicesImport();
        $import->startExecutionTime();

        Excel::import($import, $request->file('file'));

        $import->logImportActivity(
            'Lorawan Devices',
            auth()->user(),
            [
                'File Name' => $request->file('file')->getClientOriginalName(),
            ]
        );

        return back()
            ->with(['import' => ['summary' => $import->summary()]]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getTemplate
     * <Function> Create a template to Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getTemplate(): BinaryFileResponse
    {

        $rows = collect([
            [
                'Room Name'       => 'Room 1',
                'Room Area Name'  => 'Living Area',
                'Device'          => 'Smart Switch',
                'Dev EUI'         => '0011223344556677',
                'Model'           => 'WS501',
                'Gateway IP'      => '123.123.123.123',
            ]
        ]);

        $headings = ['Room Name', 'Room Area Name', 'Device', 'Dev EUI', 'Model', 'Gateway IP'];
        return Excel::download(new Export($rows, $headings), Export::getFileName('lorawan_devices_template'));
    }
}
