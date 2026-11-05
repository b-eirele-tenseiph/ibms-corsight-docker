<?php

namespace App\Http\Controllers\User\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\UserDashboard\UserDashboardService;
use Illuminate\Http\JsonResponse;
use Inertia\Response;

class DashboardController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // index                   (1.0)  Display the user's room dashboard page.
    // getSwitches             (2.0)  Retrieve room switch status.
    // getThermostat           (3.0)  Retrieve room thermostat data.
    // getDevices              (4.0)  Retrieve connected devices.
    // getSensors              (5.0)  Retrieve sensor data.
    // getPeopleCounter        (6.0)  Retrieve people counter data.
    // getOccuTempLight        (7.0)  Retrieve occupancy temp humid sensor data.
    // getCO2TempHumid         (8.0)  Retrieve CO2 temp humid sensor data.
    // getCurtains             (9.0)  Retrieve curtain status.
    // getBlindTilt            (10.0) Retrieve blind tilt status.
    // getOutdoorMeter         (11.0) Retrieve outdoor meter readings.
    // getAircon               (12.0) Retrieve air conditioning data.
    // getProjector            (13.0) Retrieve projector status.
    // getTelevision           (14.0) Retrieve television status.
    // hidePromo               (15.0) Update the hiding of promo popup into
    //                                truthy value of the currently logged-in
    //                                user.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> index
     * <Function> Display the user's room dashboard page.
     *
     * @return \Inertia\Response
     */
    public function index(UserDashboardService $service): Response
    {
        $user = auth()->user();

        return inertia('user/dashboard/Index', [
            'room'      => $service->getRoom(),
            'theme'     => config('scenes'),
            'icon'      => config('icons'),
            'filters'   => request()->only('area'),
            'hidePromo' => $user->hide_promo_popup,
            'promos'    => $user->hide_promo_popup ? [] : $service->getAllPromos(),
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getSwitches
     * <Function> Retrieve room switch status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSwitches(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomSwitches());
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getThermostat
     * <Function> Retrieve room thermostat data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getThermostat(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomThermostat());
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getDevices
     * <Function> Retrieve connected devices.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDevices(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomDevices());
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getSensors
     * <Function> Retrieve sensor data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSensors(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomSensors());
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getPeopleCounter
     * <Function> Retrieve people counter data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPeopleCounter(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomPeopleCounter());
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getOccuTempLight
     * <Function> Retrieve occupancy temp humid sensor data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOccuTempLight(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomOccuTempLight());
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getCO2TempHumid
     * <Function> Retrieve CO2 temp humid sensor data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCO2TempHumid(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomCO2TempHumid());
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getCurtains
     * <Function> Retrieve curtain status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurtains(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomCurtains());
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getBlindTilt
     * <Function> Retrieve blind tilt status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBlindTilt(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomBlindTilt());
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getOutdoorMeter
     * <Function> Retrieve outdoor meter readings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOutdoorMeter(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomOutdoorMeter());
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> getAircon
     * <Function> Retrieve air conditioning data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAircon(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomAircon());
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> getProjector
     * <Function> Retrieve projector status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjector(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomProjector());
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> getTelevision
     * <Function> Retrieve television status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTelevision(UserDashboardService $service): JsonResponse
    {
        return response()->json($service->getRoomTelevision());
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> hidePromo
     * <Function> Update the hiding of promo popup into truthy value
     *            of the currently logged-in user.
     *
     */
    public function hidePromo()
    {
        $user = auth()->user();

        $user->update([
            'hide_promo_popup' => true,
        ]);
    }
}
