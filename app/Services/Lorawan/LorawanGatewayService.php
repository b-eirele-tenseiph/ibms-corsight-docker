<?php

namespace App\Services\Lorawan;

use App\Imports\LorawanGatewaysImport;
use App\Models\LorawanGateway;
use App\Models\Floor;
use Illuminate\Http\Request;
use App\Exports\BaseExport as Export;
use App\Exports\BaseExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Laravel\Prompts\info;

class LorawanGatewayService
{

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getGateways             (1.0) Retrieves a paginated list of gateways with
    //                               sorting, filtering, and search options.
    // getFloors               (2.0) Retrieves all floors with their associated
    //                               gateways.
    // export                  (3.0) Exports gateways to Excel.
    // import                  (4.0) Imports gateways from Excel.
    // template                (5.0) Retrieves a template for importing gateways
    //                               from Excel.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getGateways
     * <Function> Retrieves a paginated list of gateways with sorting, filtering,
     *            and search options
     *
     */
    public function getGateways(Request $request)
    {
        $filters = $request->only('search', 'floor');
        $allowedSortFields = ['ip_address', 'mac_address', 'connection', 'floor'];
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // If the sort field isn't allowed, default to 'created_at'
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query = LorawanGateway::query()
            ->with(['floor'])
            ->withCount('devices')
            ->OnFloor($request->query('floor'))
            ->when(
                $filters['search'] ?? null,
                fn($q, $search) =>
                $q->where(function ($query) use ($search) {
                    $query->where('ip_address', 'like', "%$search%")
                        ->orWhere('mac_address', 'like', "%$search%")
                        ->orWhere('connection', 'like', "%$search%")
                        ->orWhereHas('floor', fn($q) => $q->where('name', 'like', "%$search%"));
                })
            )
            ->when(
                $sortField === 'floor',
                fn($q) => $q->leftJoin('floors', 'lorawan_gateways.floor_id', '=', 'floors.id')
                    ->orderBy('floors.name', $sortDirection)
                    ->select('lorawan_gateways.*')
            )
            ->when(
                $sortField === 'ip_address',
                fn($q) => $q->orderByRaw('INET_ATON(ip_address) ' . $sortDirection)
            )
            ->when(
                $sortField === 'connection',
                fn($q) => $q->orderBy($sortField, $sortDirection)
            )
            ->when(
                in_array($sortField, ['mac_address']),
                fn($q) => $q->orderBy($sortField, $sortDirection)
            );

        // Execute the query and paginate results
        return $query->paginate(10)->withQueryString();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getFloors
     * <Function> Retrieves all floors with their associated gateways
     *
     */
    public function getFloors()
    {
        return Floor::all();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> export
     * <Function> Exports all Gateways to Excel
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): BinaryFileResponse
    {
        $rows = LorawanGateway::with('floor')->get()->map(fn($g) => [
            'IP Address'  => $g->ip_address,
            'MAC Address' => $g->mac_address,
            'Floor Name'    => $g->floor?->name,
            'Connection'  => $g->connection,
            'Date Created'  => $g->created_at,
        ]);

        $headings = ['IP Address', 'MAC Address', 'Floor Name', 'Connection', 'Date Created'];

        $export = new BaseExport($rows, $headings);

        return $export->download('lorawan_gateways', auth()->user());
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> import
     * <Function> Imports Gateways from Excel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', File::types(['xlsx', 'xls', 'csv'])->max(5 * 1024)],
        ]);

        $import = new LorawanGatewaysImport();
        $import->startExecutionTime();

        Excel::import($import, $data['file']);

        $import->logImportActivity(
            'Lorawan Gateways',
            auth()->user(),
            [
                'File Name' => $request->file('file')->getClientOriginalName(),
            ]
        );

        return back()
            ->with(['import' => ['summary' => $import->summary()]]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> template
     * <Function> Create a template to Excel.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function template(): BinaryFileResponse
    {
        $rows = collect([
            [
                'IP Address'  => '123.123.123.123',
                'MAC Address' => '00:11:22:33:44:55',
                'Floor Name'    => '1st floor',
                'Connection'  => 'milesight',
            ]
        ]);

        $headings = ['IP Address', 'MAC Address', 'Floor Name', 'Connection'];
        return Excel::download(new Export($rows, $headings), Export::getFileName('lorawan_gateways_template'));
    }
}
