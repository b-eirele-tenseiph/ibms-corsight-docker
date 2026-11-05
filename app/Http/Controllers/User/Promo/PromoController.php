<?php

namespace App\Http\Controllers\User\Promo;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Inertia\Response;

class PromoController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // index                    (1.0) Return view with list of promos.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> index
     * <Function> Return view with list of promos.
     *
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        $promos = Promo::latest()
            ->whereDate('valid_start_date', '<=', now())
            ->whereDate('valid_end_date', '>=', now())
            ->get(['id', 'title', 'description', 'photo_file_path']);

        return inertia('user/promo/Index', [
            'promos' => $promos,
        ]);
    }
}
