<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class Scheduler extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __invoke                         (1.0) Handle the incoming request.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __invoke
     * <Function> Handle the incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @return void
     */
    public function __invoke(Request $request): void
    {
        Artisan::call('schedule:run');
    }
}
