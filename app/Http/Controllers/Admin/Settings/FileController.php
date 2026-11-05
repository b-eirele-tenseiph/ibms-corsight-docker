<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\DeleteRequest;
use App\Models\File as ReportFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // download       (1.0) Downloads a report file if it exists.
    // checkFile      (2.0) Checks if a report file exists.
    // destroy        (3.0) Soft deletes a report file.
    // restore        (4.0) Restores a soft-deleted report file.
    // forceDelete    (5.0) Permanently deletes a report file from the database.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> download
     * <Function> Downloads a report file if it exists.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function download(Request $request): mixed
    {
        $filename = basename($request->input('file'));

        return Storage::disk('gcs')->download(
            "reports/$filename",
            $filename,
            [
                'Cache-Control' => 'no-cache, no-store, must-revalidate'
            ]
        );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> checkFile
     * <Function> Checks if a report file exists.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkFile(Request $request): RedirectResponse
    {
        $filename = basename($request->input('file'));
        $file = "reports/$filename";

        if (!Storage::disk('gcs')->exists($file)) {
            return back()->withErrors(['message' => __('flash.not_found')]);
        }

        return back();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> destroy
     * <Function> Soft deletes a report file.
     *
     * @param \App\Models\File $file
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ReportFile $file): RedirectResponse
    {
        $file->delete();

        return redirect()->back()
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'file'])
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> restore
     * <Function> Restores a soft-deleted report file.
     *
     * @param \App\Models\File $file
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ReportFile $file): RedirectResponse
    {
        $file = ReportFile::withTrashed()->where('id', $file->id)->firstOrFail();
        $file->restore();

        return redirect()->back()
            ->with(Message::success(
                __('flash.restored', ['attrib' => 'file'])
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> forceDelete
     * <Function> Permanently deletes a report file from the database.
     *
     * @param \App\Http\Requests\File\DeleteRequest $request
     * @param \App\Models\File $file
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete(DeleteRequest $request, ReportFile $file): RedirectResponse
    {
        $file->forceDelete();

        return redirect()->back()
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'file'])
            ));
    }
}
