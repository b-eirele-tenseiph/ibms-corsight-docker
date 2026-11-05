<?php

namespace App\Http\Controllers\User\Amenities;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenitiesController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // index                    (1.0)  Display the user's room dashboard page.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> index
     * <Function> Display the user's room dashboard page.
     *
     */
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('room:id,name');
        $roomName = optional($user->room)->name;

        $amenities = Amenity::with([
            'amenityCategory:id,name',
            'photos:id,amenity_id,file_path',
        ])->latest()->get();

        $activeAmenity = null;
        if ($request->filled('show')) {
            $activeAmenity = Amenity::with([
                'amenityCategory:id,name',
                'photos:id,amenity_id,file_path',
            ])->find($request->input('show'));
        }

        return inertia('user/amenities/Index', [
            'amenities'     => $amenities,
            'activeAmenity' => $activeAmenity,
            'roomName'      => $roomName,
        ]);
    }
}
