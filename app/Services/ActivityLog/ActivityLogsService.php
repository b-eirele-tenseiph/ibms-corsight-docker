<?php

namespace App\Services\ActivityLog;

use App\Exports\BaseExport;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityLogsService
{

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // buildLogsQuery                     (1.0)  Builds activity logs query.
    // getLogs                            (2.0)  Fetch activity logs page.
    // getFilterOptions                   (3.0)  Fetch filter options for activity logs.
    // getExport                          (4.0)  Handles exporting all Activity Logs.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> buildLogsQuery
     * <Function> Builds activity logs query.
     *
     */
    private function buildLogsQuery(Request $request): Builder
    {
        $query = Activity::query()->with('causer')
            // Search
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                if (is_numeric($search)) {
                    $q->orderByRaw("CASE WHEN id = ? THEN 0 ELSE 1 END", [$search]);
                }
                $q->where(function ($sub) use ($search) {
                    $sub->where('id', 'like', "%{$search}%")
                        ->orWhere('log_name', 'like', "%{$search}%")
                        ->orWhere('causer_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            // Filter: Severity
            ->when($request->filled('severity'), fn($q) => $q->where('log_name', $request->input('severity')))
            // Filter: Origin Type
            ->when($request->filled('origin_type'), fn($q) => $q->where('causer_type', 'like', '%' . $request->input('origin_type') . '%'))
            // Filter: Date Range
            ->when($request->filled('date_from') && $request->filled('date_to'), fn($q) => $q->whereBetween('updated_at', [
                $request->input('date_from'),
                $request->input('date_to'),
            ]));

        // Sorting
        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');

        if (in_array($sortField, ['updated_at', 'causer_id', 'log_name', 'causer_type'], true)) {
            $query->orderBy($sortField, $sortDirection);
        } elseif ($sortField === 'properties.code') {
            $query->orderByRaw("
        COALESCE(
            JSON_UNQUOTE(JSON_EXTRACT(properties, '$.code')),
            JSON_UNQUOTE(JSON_EXTRACT(properties, '$.Code'))
        ) $sortDirection
    ");
        } else {
            $query->latest();
        }

        return $query;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getLogs
     * <Function> Fetch activity logs page.
     *
     */
    public function getLogs(Request $request)
    {
        $logs = $this->buildLogsQuery($request)
            ->paginate(10)
            ->withQueryString();

        // collection transformation for causer_type
        $logs->getCollection()->transform(function ($log) {
            $log->causer_type = class_basename($log->causer_type);
            $log->causer_name = optional($log->causer)->name ?? 'System';
            return $log;
        });

        return $logs;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getFilterOptions
     * <Function> Fetch filter options for activity logs.
     *
     */
    public function getFilterOptions()
    {
        return Activity::select('log_name', 'causer_type')
            ->distinct()
            ->get()
            ->map(fn($log) => [
                'log_name'    => $log->log_name,
                'causer_type' => class_basename($log->causer_type),
            ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getExport
     * <Function> Handles exporting all Activity Logs.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getExport(Request $request): BinaryFileResponse
    {
        $rows = $this->buildLogsQuery($request)
            ->get()
            ->map(fn($log) => [
                'ID'          => $log->id,
                'Code'        => $log->properties->get('Code'),
                'Severity'    => $log->log_name,
                'Origin'      => class_basename($log->causer_type),
                'Description' => $log->description,
                'Properties' => $log->properties->toJson(JSON_PRETTY_PRINT),
                'Date Created' => $log->created_at?->timezone('Asia/Manila')->toDateTimeString(),
            ]);

        $headings = ['ID', 'Code', 'Severity', 'Origin', 'Description', 'Properties', 'Date Created'];

        $export = new BaseExport($rows, $headings);

        return $export->download('activity_logs', auth()->user());
    }
}
