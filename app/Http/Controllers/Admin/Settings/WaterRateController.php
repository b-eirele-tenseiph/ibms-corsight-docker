<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WaterRate\UpdateRequest;
use App\Http\Requests\WaterRate\StoreRequest;
use App\Models\WaterRate;
use App\Services\FlashMessage\FlashMessageService as Message;
use Illuminate\Http\RedirectResponse;

class WaterRateController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // store            (1.0) Create new water rate.
    // update           (2.0) Update existing water rate.
    // destroy          (3.0) Delete existing water rate.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> store
     * <Function> Create new water rate.
     *
     * @param \App\Http\Requests\WaterRate\StoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        WaterRate::create($request->validated());

        return redirect()
            ->route('admin.management.settings.system.rate-index')
            ->with(Message::success(
                __('flash.added', ['attrib' => 'water rate'])
            ));
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> update
     * <Function> Update existing water rate.
     *
     * @param \App\Http\Requests\WaterRate\UpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateRequest $request, WaterRate $rate): RedirectResponse
    {
        $rate->update($request->validated());

        return redirect()
            ->route('admin.management.settings.system.rate-index')
            ->with(Message::success(
                __('flash.updated', ['attrib' => 'water rate'])
            ));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> destroy
     * <Function> Delete existing water rate.
     *
     * @param \App\Models\WaterRate $rate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(WaterRate $rate): RedirectResponse
    {
        $count = WaterRate::where('consumer_type', $rate->consumer_type)
            ->where('meter_size', $rate->meter_size)
            ->count();

        if ($count <= 1) {
            return redirect()
                ->back()
                ->with(Message::error(
                    __('flash.cannot_delete_last_water_rate')
                ));
        }

        $rate->delete();

        return redirect()
            ->route('admin.management.settings.system.rate-index')
            ->with(Message::success(
                __('flash.deleted', ['attrib' => 'water rate'])
            ));
    }
}
