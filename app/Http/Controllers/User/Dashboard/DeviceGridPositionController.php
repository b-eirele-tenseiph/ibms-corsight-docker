<?php

namespace App\Http\Controllers\User\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceGridPosition\StoreRequest;
use App\Models\DeviceGridPosition;
use Illuminate\Http\RedirectResponse;

class DeviceGridPositionController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // store                 (1.0) Store device positions in storage.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> store
     * <Function> Store device positions in storage.
     *
     * @param  \App\Http\Requests\DeviceGridPosition\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $request->computed()->each(function ($position) {
            DeviceGridPosition::updateOrCreate(
                $position['needle'],
                $position['content']
            );
        });

        return back();
    }
}
