<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreRequest;
use App\Services\Report\ElectricMonitoringReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;

class ElectricMonitoringReportController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // printReport            (1.0)  Generates and returns the electric
    //                               monitoring report view with preloaded data.
    // generateReport         (2.0)  Dispatches a job to generate and return the
    //                               electric monitoring report.
    // retryReport            (3.0)  Retries generating the electric monitoring
    //                               report.
    // downloadReport         (4.0)  Downloads the generated electric monitoring
    //                               report from Google Cloud Storage.
    // getSummaryData         (5.0)  Gets the summary data for the electric
    //                               monitoring report.
    // getTotalConsumption    (6.0)  Gets the total consumption per meter data
    // PerMeterData                  for the electric monitoring report.
    // getOverallConsumption  (7.0)  Gets the overall consumption report
    // ReportData                    data for the electric monitoring report.
    // getMonthlyConsumption  (8.0)  Gets the monthly consumption data for the
    // Data                          electric monitoring report.
    // getHourlyConsumption   (9.0)  Gets the hourly consumption data for the
    // Data                          electric monitoring report.
    // getEnergyCostData      (10.0) Gets the energy cost data for the electric
    //                               monitoring report.
    // getCarbonFootprintData (11.0) Gets the carbon footprint data for the
    //                               electric monitoring report.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> printReport
     * <Function> Generates and returns the electric monitoring report view with preloaded data.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Inertia\Response
     */
    public function printReport(StoreRequest $request, string $jobId, ElectricMonitoringReportService $service): Response
    {
        $validated = $request->validated();
        $data = $service->getReportData($validated, $jobId);

        return inertia('admin/electric-monitoring-report/Index', $data);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> generateReport
     * <Function> Dispatches a job to generate and return the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateReport(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        $validated = $request->validated();
        $result = $service->getGeneratedReport($validated);

        return response()->json($result);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> retryReport
     * <Function> Retries generating the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function retryReport(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return $this->generateReport($request, $service);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> downloadReport
     * <Function> Downloads the generated electric monitoring report from Google Cloud Storage.
     *
     * @param string $filename
     * @return mixed
     */
    public function downloadReport(string $filename): mixed
    {
        $file = "reports/{$filename}";

        if (!Storage::disk('gcs')->exists($file)) {
            abort(404);
        }

        return Storage::disk('gcs')->download($file, $filename, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getSummaryData
     * <Function> Gets the summary data for the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummaryData(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return response()->json($service->loadSummaryData($request->validated()));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getTotalConsumptionPerMeterData
     * <Function> Gets the total consumption per meter data for the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalConsumptionPerMeterData(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return response()->json($service->loadTotalConsumptionPerMeter($request->validated()));
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getOverallConsumptionReportData
     * <Function> Gets the overall consumption report data for the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOverallConsumptionReportData(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return response()->json($service->loadConsumptionReportData($request->validated()));
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getMonthlyConsumptionData
     * <Function> Gets the monthly consumption data for the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthlyConsumptionData(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return response()->json($service->loadMonthlyConsumptionData($request->validated()));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getHourlyConsumptionData
     * <Function> Gets the hourly consumption data for the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHourlyConsumptionData(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return response()->json($service->loadHourlyConsumptionData($request->validated()));
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getEnergyCostData
     * <Function> Gets the energy cost data for the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEnergyCostData(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return response()->json($service->loadDailyEnergyCost($request->validated()));
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getCarbonFootprintData
     * <Function> Gets the carbon footprint data for the electric monitoring report.
     *
     * @param \App\Http\Requests\Report\StoreRequest $request
     * @param \App\Services\Report\ElectricMonitoringReportService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCarbonFootprintData(StoreRequest $request, ElectricMonitoringReportService $service): JsonResponse
    {
        return response()->json($service->loadCarbonFootprintData($request->validated()));
    }
}
