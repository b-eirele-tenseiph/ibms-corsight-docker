<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Notifications\AdminNotification;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     *  The authenticated user.
     *
     * @return \Illuminate\Foundation\Auth\User
     */
    private $user;
    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $this->getAuthenticatedData($request),
            ],
            'impersonation' => [
                'active'     => session()->has('impersonating_role'),
                'role'       => session('impersonating_role'),
            ],
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
            ],
            'import' => [
                'summary' => fn() => optional($request->session()->get('import'))['summary'] ?? null,
            ],
            'unread_notifications' => fn() => $this->getUnreadNotifications($request),
        ];
    }


    /**
     * Returns an array of user-specific data to be shared with the front-end.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|null
     */
    private function getAuthenticatedData(Request $request): ?array
    {
        if (is_null($request->user())) {
            return null;
        }

        $this->user = $request->user();
        $room = $this->user->room;

        return [
            'id'            => $this->user->id,
            'role'          => session('original_role') ?? $this->user->role_name,
            'room_id'       => $room?->id,
            'room_number'   => $room?->number,
            'room_name'     => $room?->name,
            'name'          => $this->user->name,
            'username'      => $this->user->username,
            'email'         => $this->user->email,
            'ads_flag'      => $this->user->ads_flag,
            'guard_name'    => $this->getCurrentAuthGuard(),
            'permissions'   => $this->user->getAllPermissions()->pluck('name') ?? [],
            'external_apps' => $room?->external_apps ?? [],
        ];
    }

    /**
     * Detects and returns the currently active authentication guard.
     *
     * @return string|null
     */
    private function getCurrentAuthGuard(): ?string
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (\Illuminate\Support\Facades\Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    /**
     * Returns the unread notifications count for the authenticated user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    private function getUnreadNotifications(Request $request): string
    {
        $user = $request->user('admin') ?? $request->user();

        if (!$user) return '0';

        $type = $user instanceof Admin
            ? AdminNotification::class
            : UserNotification::class;

        $count = $user->unreadNotifications()
            ->where('type', $type)
            ->count();

        return $count > 99 ? '99+' : (string) $count;
    }
}
