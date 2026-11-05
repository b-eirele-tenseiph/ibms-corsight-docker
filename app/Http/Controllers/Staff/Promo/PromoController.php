<?php

namespace App\Http\Controllers\Staff\Promo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promo\StoreRequest;
use App\Http\Requests\Promo\UpdateRequest;
use App\Models\Promo;
use App\Services\FlashMessage\FlashMessageService as Message;
use App\Services\Promo\PromoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromoController extends Controller
{
    /*************************************************************************/
    /* Processing Hierarchy                                                  */
    /*************************************************************************/
    // __construct          (1.0) Delegate Services across controller
    // index                (2.0) Display the promo management page along with
    //                            paginated promo records.
    // store                (3.0) Create a new promo and store to database.
    // update               (4.0) Update the specified promo in database.
    // destroy              (5.0) Remove the specified promo from database.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Delegate Services across controller
     *
     * @param \App\Services\Promo\PromoService $promoService
     *
     * @return \Inertia\Response
     */
    public function __construct(
        protected PromoService $promoService
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display the promo management page along with
     *            paginated promo records.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        return Inertia::render(
            'staff/promo/Index',
            [
                'promos' => $this->promoService->getPromos($request->query()),
                'queryParams' => $request->query()
            ]
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Create a new promo and store to database.
     *
     * @param \App\Http\Requests\Promo\StoreRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request)
    {
        $this->promoService->createPromo($request->promoData());

        return back()->with(Message::success(
            __(
                'flash.added',
                ['attrib' => 'Promo']
            )
        ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified promo in database.
     *
     * @param \App\Http\Requests\Promo\UpdateRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, Promo $promo)
    {
        $photoFile = $request->uploadPhoto();

        $this->promoService->updatePromo(
            $request->promoData(),
            $promo,
            $photoFile
        );

        return back()
            ->with(Message::success(
                __(
                    'flash.updated',
                    ['attrib' => 'Promo']
                )
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified promo from database.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Promo $promo)
    {
        $this->promoService->deletePromo($promo);

        return back()
            ->with(Message::success(
                __(
                    'flash.deleted',
                    ['attrib' => 'Promo']
                )
            ));
    }
}
