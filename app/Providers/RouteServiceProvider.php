<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    /**************************************************************************************/
    /* Processing Hierarchy                                                               */
    /**************************************************************************************/
    // register()                        (1.0) Register services required by the application.
    // boot()                            (2.0) Bootstrap any application services.
    // getHome()                         (3.0) Get the default home path for users.
    // getAdminHome()                    (4.0) Get the default home path based on admin roles.

    /**
     * <Layer number> (1.0)
     *
     * Processing name: register
     * Function: Register services required by the application.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * <Layer number> (2.0)
     *
     * Processing name: boot
     * Function: Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * <Layer number> (3.0)
     *
     * Processing name: getHome
     * Function: Get the default dashboard path for a user user.
     *
     * @return string The dashboard path for the user.
     */
    public static function getHome(): string
    {
        $number = optional(auth()->user()->room)->number;

        return "/user/{$number}/dashboard";
    }

    /**
     * <Layer number> (4.0)
     *
     * Processing name: getAdminHome
     * Function: Get the appropriate dashboard path for an admin based on their role.
     *
     * @return string The dashboard path for the admin.
     */
    public static function getAdminHome(): string
    {
        $role = optional(auth('admin')->user()->role)->name;

        switch ($role) {
            case 'staff':
                return "staff/front-desk/dashboard";
            case 'crew':
                return "crew/dashboard";
            default:
                return 'admin/dashboard';
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {

        // RateLimiter::for('api', function (Request $request) {
        //     return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        // });

        RateLimiter::for('api', function (Request $request) {
            // Exclude the Cloud Task endpoint from rate limiting
            if ($request->routeIs('cloudtask.endpoint')) {
                return Limit::none();
            }
            // Apply default rate limit to other routes
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
