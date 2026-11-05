<?php

namespace App\Http\Controllers\Admin\ElectricMonitoring;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElectricMonitoring\FormRequest;
use App\Models\LorawanDevice;
use App\Services\ElectricMonitoring\ElectricMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ElectricMonitoringController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $service                        (1.0)  Instance for handling electric
    //                                        monitoring related operations.
    // __construct                     (2.0)  Initialize the controller with the
    //                                        electric monitoring service.
    // index                           (3.0)  Display the electric monitoring
    //                                        page.
    // getElectricMeterTotalData       (4.0)  Get the current total data from
    //                                        electric meter.
    // getElectricMeterConsumptionData (5.0)  Get current consumption data from
    //                                        electric meter.
    // getElectricMeterCurrentData     (6.0)  Get current data from electric
    //                                        meter.
    // getElectricMeterVoltageData     (7.0)  Get voltage data from electric
    //                                        meter.
    // getElectricMeterPowerData       (8.0)  Get power data from electric meter
    // getElectricMeterPowerFactorData (9.0)  Get the power factor data from
    //                                        electric meter.
    // getElectricMeterFrequencyData   (10.0) Get the frequency data from
    //                                        electric meter.
    // getInsightsData                 (11.0) Get the insights data from
    //                                        electric meter.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $service
     * <Function> Instance for handling electric monitoring related operations.
     *
     * @var \App\Services\ElectricMonitoring\ElectricMonitoringService
     */
    private ElectricMonitoringService $service;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Initialize the controller with the electric monitoring service.
     *
     * @param ElectricMonitoringService $service
     */
    public function __construct(ElectricMonitoringService $service)
    {
        $this->service = $service;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> index
     * <Function> Display the electric monitoring page.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        return inertia('admin/electric-monitoring/Index', [
            'electric_meter' => LorawanDevice::deviceType('electricMeter')
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'created_at', 'updated_at']),
            'filters'   => (object) $request->query()
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getElectricMeterTotalData
     * <Function> Get the current total data from electric meter.
     *
     */
    public function getElectricMeterTotalData(Request $request): JsonResponse
    {
        $data = $this->service->loadTotalData($request->input('device'));

        return response()->json($data);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getElectricMeterConsumptionData
     * <Function> Get the current consumption data from electric meter.
     *
     * @param \App\Http\Requests\ElectricMonitoring\FormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElectricMeterConsumptionData(FormRequest $request): JsonResponse
    {
        $data = $this->service->loadConsumptionData($request->deviceId(), $request->time());
        return response()->json($data);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getElectricMeterCurrentData
     * <Function> Get the current data from electric meter.
     *
     * @param \App\Http\Requests\ElectricMonitoring\FormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElectricMeterCurrentData(FormRequest $request): JsonResponse
    {
        $data = $this->service->loadCurrentData($request->deviceId(), $request->time());
        return response()->json($data);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getElectricMeterVoltageData
     * <Function> Get the voltage data from electric meter.
     *
     * @param \App\Http\Requests\ElectricMonitoring\FormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElectricMeterVoltageData(FormRequest $request): JsonResponse
    {
        $data = $this->service->loadVoltageData($request->deviceId(), $request->time(), $request->phase());
        return response()->json($data);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getElectricMeterPowerData
     * <Function> Get the power data from electric meter.
     *
     * @param \App\Http\Requests\ElectricMonitoring\FormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElectricMeterPowerData(FormRequest $request): JsonResponse
    {
        $data = $this->service->loadPowerData($request->deviceId(), $request->time());
        return response()->json($data);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getElectricMeterPowerFactorData
     * <Function> Get the power factor data from electric meter.
     *
     * @param \App\Http\Requests\ElectricMonitoring\FormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElectricMeterPowerFactorData(FormRequest $request): JsonResponse
    {
        $data = $this->service->loadPowerFactorData($request->deviceId(), $request->time());
        return response()->json($data);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getElectricMeterFrequencyData
     * <Function> Get the frequency data from electric meter.
     *
     * @param \App\Http\Requests\ElectricMonitoring\FormRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElectricMeterFrequencyData(FormRequest $request): JsonResponse
    {
        $data = $this->service->loadFrequencyData($request->deviceId(), $request->time());
        return response()->json($data);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getInsightsData
     * <Function> Get the insights data from electric meter.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInsightsData(Request $request): JsonResponse
    {
        $data = $this->service->loadDataInsights($request->input('device'));
        return response()->json($data);
    }
}
