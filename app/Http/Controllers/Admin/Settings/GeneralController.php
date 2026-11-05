<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Services\Settings\FileService;
use App\Services\Settings\RateService;
use Illuminate\Http\Request;
use Inertia\Response;

class GeneralController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct     (1.0) Initializes the controller with required service
    //                       dependencies.
    // general         (2.0) List general settings including electricity rates.
    // rates           (3.0) Displays the electricity and water rates management
    //                       page.
    // file            (4.0) Displays the file management page.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initializes the controller with required service dependencies.
     *
     * @param \App\Services\Settings\RateService $rateService
     */
    public function __construct(
        protected RateService $rateService,
        protected FileService $fileService
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> List general settings including electricity rates.
     *
     * @return \Inertia\Response
     */
    public function general(): Response
    {
        return inertia('admin/settings-general/Index');
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> rates
     * <Function> Displays the electricity and water rates management page.
     * @return \Inertia\Response
     */
    public function rates(): Response
    {
        return inertia(
            'admin/settings-rate/Index',
            $this->rateService->getRateData()
        );
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> file
     * <Function> Displays the file management page.
     *
     * @return \Inertia\Response
     */
    public function file(Request $request): Response
    {
        return inertia(
            'admin/settings-file/Index',
            $this->fileService->getFileData($request)
        );
    }
}
