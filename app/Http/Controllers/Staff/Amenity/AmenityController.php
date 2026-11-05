<?php

namespace App\Http\Controllers\Staff\Amenity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Amenity\StoreRequest;
use App\Http\Requests\Amenity\UpdateRequest;
use App\Models\Amenity;
use App\Services\Amenity\AmenityService;
use Illuminate\Http\Request;
use App\Services\FlashMessage\FlashMessageService as Message;
use Inertia\Inertia;

class AmenityController extends Controller
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // __construct          (1.0) Delegate Services across controller
    // index                (2.0) Display the amenity management page along with
    //                            paginated amenity records.
    // store                (3.0) Create a new amenity and store to database.
    // update               (4.0) Update the specified amenity in database.
    // destroy              (5.0) Remove the specified amenity from database.
    // toggleVisibility     (6.0) Toggle visibility of an amenity.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Delegate Services across controller
     *
     * @param \App\Services\Amenity\AmenityService $amenityService
     *
     */
    public function __construct(
        protected AmenityService $amenityService,
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display the amenity management page along with paginated
     *            amenity records.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        return Inertia::render(
            'staff/amenity/Index',
            [
                'amenities'         => $this->amenityService->getAmenities($request->query()),
                'amenityCategories' => $this->amenityService->getAmenityCategories(),
                'queryParams'       => $request->query()
            ]
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Create a new amenity and store to database.
     *
     * @param \App\Http\Requests\Amenity\StoreRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request)
    {
        // Capture the created model from the service
        $amenity = $this->amenityService->createAmenity(
            $request->amenityData(),
            $request->uploadPhotos()
        );

        return back()
            ->with(Message::success(
                __(
                    'flash.added',
                    ['attrib' => 'Amenity']
                )
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified amenity in database.
     *
     * @param \App\Http\Requests\Amenity\UpdateRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, Amenity $amenity)
    {
        $uploadedNewPhotoPaths = $request->uploadPhotos();
        $existingPhotoIds = (array) $request->input('existing_photo_ids', []);

        $this->amenityService->updateAmenity(
            $request->amenityData(),
            $amenity,
            $uploadedNewPhotoPaths,
            $existingPhotoIds
        );

        return back()
            ->with(Message::success(
                __(
                    'flash.updated',
                    ['attrib' => 'Amenity']
                )
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified amenity from database.
     *
     * @param  \App\Models\Amenity  $amenity
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Amenity $amenity)
    {
        $this->amenityService->deleteAmenity($amenity);

        return back()
            ->with(Message::success(
                __(
                    'flash.deleted',
                    ['attrib' => 'Amenity']
                )
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> toggleVisibility
     * <Function> Toggle visibility of an amenity.
     *
     */
    public function toggleVisibility(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'is_visible' => ['required', 'boolean'],
        ]);

        $amenity->update(['is_visible' => (bool) $validated['is_visible']]);

        return back();
    }
}
