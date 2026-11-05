<?php

namespace App\Services\Holiday;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhilippineHolidayService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // API_URL        (1.0) The Nager.Date API URL for Philippine holidays.
    // isHoliday      (2.0) Check if a given date is a Philippine holiday.
    // getHolidays    (3.0) Get all Philippine holidays for a given year.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> API_URL
     * <Function> The Nager.Date API URL for Philippine holidays.
     *
     * @var string
     */
    const API_URL = 'https://date.nager.at/api/v3/PublicHolidays/{year}/PH';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> isHoliday
     * <Function> Check if a given date is a Philippine holiday.
     *
     * @param  \Carbon\Carbon $date
     * @return bool
     */
    public function isHoliday(Carbon $date): bool
    {
        return in_array($date->toDateString(), $this->getHolidays($date->year));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getHolidays
     * <Function> Get all Philippine holidays for a given year.
     *            Cached for 30 days to avoid repeated API calls.
     *
     * @param  int   $year
     * @return array
     */
    public function getHolidays(int $year): array
    {
        return Cache::remember("ph_holidays_{$year}", now()->addDays(30), function () use ($year) {
            $url = str_replace('{year}', $year, self::API_URL);

            $response = Http::get($url);

            if ($response->failed()) {
                Log::warning("PhilippineHolidayService: Failed to fetch holidays for {$year}.");
                return [];
            }

            return collect($response->json())
                ->pluck('date')
                ->toArray();
        });
    }
}
