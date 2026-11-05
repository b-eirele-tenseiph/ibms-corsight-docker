<?php

use App\Http\Middleware\CheckImpersonation;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleLocalization;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'locale', 'timezone']);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'only' => App\Http\Middleware\AllowSpecificGuard::class,
            'admin.enabled' => App\Http\Middleware\CheckEnabled::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'allowed_access' => \App\Http\Middleware\RoomAccessIsNotAllowed::class,
            'appengine.cron' => App\Http\Middleware\AppEngineCronRequest::class,
            'reset.validity' => \App\Http\Middleware\EnsureValidResetToken::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'api' => 'throttle:api',
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleLocalization::class,
            CheckImpersonation::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Generic handler registered FIRST
        $exceptions->render(function (Throwable $e, Request $request) {
            return app(\App\Exceptions\Handler::class)->render($request, $e);
        });
    })->create();
