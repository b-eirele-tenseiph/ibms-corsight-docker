<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\Emergency\EmergencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FlashMessage\FlashMessageService as Message;

class EmergencyController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // roomEmergency             (1.0) Trigger emergency and notify staff admin.

    /**
     *
     * <Layer number> (1.0)
     *
     * <Processing name> roomEmergency
     * <Function> Trigger emergency and notify staff admin.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function roomEmergency(Request $request, EmergencyService $emergency)
    {
        $command = (string) $request->input('command');
        $emergency->dispatch(Auth::user(), $command);

        return redirect()->back()->with(Message::error(__('flash.emergency')));
    }
}
