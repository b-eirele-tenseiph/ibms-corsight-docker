<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElectricityRate\StoreRequest;
use App\Http\Requests\ElectricityRate\UpdateRequest;
use App\Models\ElectricityRate;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\RedirectResponse;

class ElectricityRateController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // store            (1.0) Create new electricity rate.
    // update           (2.0) Update existing electricity rate.
    // destroy          (3.0) Delete existing electricity rate.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> store
     * <Function> Create new electricity rate.
     *
     * @param \App\Http\Requests\ElectricityRate\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        ElectricityRate::create($request->validated());

        return redirect()
            ->route('admin.management.settings.system.rate-index')
            ->with(Message::success(
                __('flash.added', ['attrib' => 'electricity rate'])
            ));
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> update
     * <Function> Update existing electricity rate.
     *
     * @param \App\Http\Requests\ElectricityRate\UpdateRequest $request
     * @param \App\Models\ElectricityRate $rate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, ElectricityRate $rate): RedirectResponse
    {
        $rate->update($request->validated());

        return redirect()
            ->route('admin.management.settings.system.rate-index')
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'electricity rate'])
            ));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> destroy
     * <Function> Delete existing electricity rate.
     *
     * @param \App\Models\ElectricityRate $rate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ElectricityRate $rate): RedirectResponse
    {
        $count = ElectricityRate::count();

        if ($count <= 1) {
            return redirect()
                ->back()
                ->with(Message::error(
                    __('flash.cannot_delete_last_rate')
                ));
        }

        $rate->delete();

        return redirect()
            ->route('admin.management.settings.system.rate-index')
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'electricity rate'])
            ));
    }
}
