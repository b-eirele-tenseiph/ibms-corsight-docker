<?php

namespace App\Http\Controllers\Admin\SwitchBotLockAccess;

use App\Http\Controllers\Controller;
use App\Models\SwitchBotLockAccess;
use Illuminate\Http\RedirectResponse;

class SwitchBotLockAccessController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // update        (1.0) Method that updates the status of lock access to
    //                      for creation.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> update
     * <Function> Method that updates the status of lock access to for creation.
     *
     * @param \App\Models\SwitchBotLockAccess $access
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SwitchBotLockAccess $access): RedirectResponse
    {
        $access->update([
            'status' => 'for creation',
            'retry'  => 0
        ]);

        return back();
    }
}
