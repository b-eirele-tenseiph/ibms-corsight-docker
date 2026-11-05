<?php

namespace App\Http\Controllers\Crew\Food;

use App\Http\Controllers\Controller;
use App\Http\Requests\Food\StoreRequest;
use App\Http\Requests\Food\UpdateRequest;
use App\Models\Food;
use App\Services\Food\FoodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FoodController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __constructor       (1.0) Constructor for initializing Food
    //                           Service
    // index               (2.0) Retrieve food list from storage and their
    //                           categories.
    // store               (3.0) Store a newly created food resource in storage.
    // update              (4.0) Update the food resource.
    // destroy             (5.0) Remove the specified resource from storage.
    //updateAvailability   (6.0) Update available status

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing FoodService
     *
     */
    public function __construct(private FoodService $foodService) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Retrieve food list from storage and their categories.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category', 'sort', 'direction', 'page']);

        ['foods' => $foods, 'categories' => $categories] =
            $this->foodService->getIndexData($filters);

        return inertia('crew/food/Index', [
            'foods' => $foods,
            'categories' => $categories,
            'filters' => (object) $filters,
        ]);
    }


    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a newly created food resource in storage.
     *
     * @param  \App\Http\Requests\Food\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        Food::create($request->foodData());

        return redirect()
            ->route('crew.foods.index')
            ->with(Message::success(
                __('flash.added', ['attrib' => 'Food'])
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the food resource.
     *
     * @param  \App\Http\Requests\Food\UpdateRequest $request
     * @param  \App\Models\Food                      $food
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, Food $food)
    {
        DB::transaction(function () use ($food, $request) {

            $data = $request->foodData();

            if (@$data['image']) {
                // Delete photo from cloud storage
                $bucket = env('GOOGLE_CLOUD_STORAGE_BUCKET');

                $urlPath = parse_url($food->image, PHP_URL_PATH);

                $path = Str::replaceFirst("/{$bucket}/", '', $urlPath);

                if (Storage::disk('gcs')->exists($path)) {
                    Storage::disk('gcs')->delete($path);
                }
            }

            $food->update($request->foodData());
        });

        return redirect()->back()
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'Food'])
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified resource from storage.
     *
     * @param  \App\Models\Food  $food
     * @return \Illuminate\Http\Response
     */
    public function destroy(Food $food): RedirectResponse
    {
        DB::transaction(function () use ($food) {
            // Delete photo from cloud storage
            $bucket = env('GOOGLE_CLOUD_STORAGE_BUCKET');

            $urlPath = parse_url($food->image, PHP_URL_PATH);

            $path = Str::replaceFirst("/{$bucket}/", '', $urlPath);

            if (Storage::disk('gcs')->exists($path)) {
                Storage::disk('gcs')->delete($path);
            }

            // Remove the specified resource held by $food
            $food->delete();
        });

        // Redirect to the index method
        return back()
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'Food'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> updateAvailability
     * <Function> Update available status
     *
     * @param  \Illuminate\Http\Request      $request
     * @param  \App\Models\Food              $food
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvailability(Request $request, Food $food): JsonResponse
    {
        $food->update([
            'available' => $request->input('available')
        ]);

        return response()->json([
            'message' => 'Updated'
        ]);
    }
}
