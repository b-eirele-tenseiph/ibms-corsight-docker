<?php

namespace App\Services\Settings;

use Illuminate\Http\Request;
use App\Models\File as ReportFile;

class FileService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getFileData        (1.0) Retrieves a paginated list of files with sorting
    //                          and filtering options.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getFileData
     * <Function> Retrieves a paginated list of files with sorting and filtering options.
     *
     * @param \Illuminate\Http\Request $request
     * @return array`
     */
    public function getFileData(Request $request): array
    {
        $filter = $request->query('data', 'all');
        $sortField = $request->query('sort', 'created_at');
        $sortDirection = $request->query('direction', 'asc');

        return [
            'files' => ReportFile::select(['id', 'name', 'extension', 'size', 'path', 'admin_id', 'created_at', 'deleted_at'])
                ->with('admin:id,name')
                ->when($filter === 'archived', fn($q) => $q->onlyTrashed())
                ->when($request->query('search'), function ($query, $search) {
                    return $query->where(function ($query) use ($search) {
                        $query->whereHas('admin', function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        })
                            ->orWhere('name', 'LIKE', "%{$search}%")
                            ->orWhere('size', 'LIKE', "%{$search}%");
                    });
                })
                ->when($sortField, function ($query) use ($sortField, $sortDirection) {
                    if ($sortField == 'admin_id') {
                        $query = $query->withAggregate('admin', 'name');
                    }
                    return $query->orderBy($sortField, $sortDirection);
                })
                ->paginate()
                ->withQueryString(),
            'filters' => $request->query(),
        ];
    }
}
