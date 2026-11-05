<?php

namespace App\Http\Controllers\Admin\Lorawan;

use App\Http\Requests\LorawanGateway\StoreRequest;
use App\Http\Requests\LorawanGateway\UpdateRequest;
use App\Models\LorawanGateway;
use App\Services\FlashMessage\FlashMessageService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Lorawan\LorawanGatewayService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class LorawanGatewayController extends Controller
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // __construct   (1.0) Constructor for initializing Lorawan Gateway Service.
    // index		 (2.0） Display a listing of the resource.
    // store		 (3.0） Store newly created resource.
    // update		（4.0） Update specified resource
    // destroy	    （5.0） Delete specified resource
    // export		（6.0） Export all Gateways to Excel
    // import		（7.0） Import Gateways from Excel
    // template	    （8.0） Export a template for importing Gateways from Excel

    /**
     * <Layer number> (1.0)
     *
     * Processing: __construct
     * Function: Constructor for initializing Lorawan Gateway Service.
     *
     */
    public function __construct(
        protected LorawanGatewayService $service
    ) {}


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Get the gateways using the service
        $gateways = $this->service->getGateways($request);

        // Get the floors using the service
        $floors = $this->service->getFloors();

        // Return the Inertia response with data
        return Inertia::render('admin/lorawan-gateway/Index', [
            'gateways' => $gateways,
            'connection' => collect(config('lorawan.connections'))->keys()->all(),
            'filters' => $request->only('search', 'floor'),
            'queryParams' => $request->query(),
            'floors' => $floors,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> store
     * <Function> Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\LorawanGateway\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        // $lorawanGateway as the new created resource
        LorawanGateway::create($request->validated());

        // Redirect to index method
        return redirect()
            ->back()
            ->with(FlashMessageService::success(
                __('flash.added', ['attrib' => 'Gateway'])
            ));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> update
     * <Function> Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\LorawanGateway\UpdateRequest $request
     * @param  \App\Models\LorawanGateway                      $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, LorawanGateway $gateway): RedirectResponse
    {
        // Updates the specified resource held by $gateway
        $gateway->update($request->validated());

        // Redirect to index method
        return redirect()->back()
            ->with(FlashMessageService::success(
                __('flash.updated', ['attrib' => 'Gateway'])
            ));
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> destroy
     * <Function> Remove the specified resource from storage.
     *
     * @param  \App\Models\LorawanGateway        $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(LorawanGateway $gateway): RedirectResponse
    {
        // Remove the specified resource held by $gateway
        $gateway->delete();

        // Redirect to the index method
        return back()
            ->with(FlashMessageService::success(
                __('flash.deleted', ['attrib' => 'Gateway'])
            ));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> export
     * <Function> Export all Gateways to Excel
     */
    public function export()
    {
        return $this->service->export();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> import
     * <Function> Import Gateways from Excel
     */
    public function import(Request $request)
    {
        return $this->service->import($request);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> template
     * <Function> Export a template for importing Gateways from Excel
     */
    public function template()
    {
        return $this->service->template();
    }
}
