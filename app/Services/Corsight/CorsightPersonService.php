<?php

namespace App\Services\Corsight;

use App\Models\CorsightDevice;
use App\Models\CorsightDeviceData;
use App\Models\CorsightPersonSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CorsightPersonService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getPeople      (1.0)  Retrieves a paginated list of person sessions
    //                       with sorting and filtering options.
    // getCounters    (2.0)  Retrieves the page-header counters for the
    //                       current filter scope.
    // getDevices     (3.0)  Retrieves the device list for the filter bar.
    // getDetail      (4.0)  Retrieves a single session with its diary trail.
    // sweepStale     (5.0)  Closes in-sessions that have gone stale —
    //                       shared by the maintenance command and the
    //                       read path (self-healing UI).
    // applyFilters   (6.0)  Applies the shared query filters.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getPeople
     * <Function> Retrieves a paginated list of person sessions with
     *            sorting and filtering options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPeople(Request $request): LengthAwarePaginator
    {
        $this->sweepStale();

        $sortField = in_array($request->query('field'), ['enter_at', 'exit_at'], true)
            ? $request->query('field')
            : 'enter_at';
        $sortDirection = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        return $this->applyFilters(CorsightPersonSession::query(), $request)
            ->with(['device.room:id,name', 'device.roomArea:id,name'])
            ->orderBy($sortField, $sortDirection)
            ->paginate(15)
            ->through(
                fn(CorsightPersonSession $session) => [
                    'id' => $session->id,
                    'status' => $session->status,
                    'enter_at' => CorsightDisplay::timestamp($session->enter_at),
                    'exit_at' => CorsightDisplay::timestamp($session->exit_at),
                    'dwell_seconds' => $session->dwell_seconds,
                    'gender' => $session->gender,
                    'age_group' => $session->age_group,
                    'face_score' => $session->face_score,
                    'image' => $session->image,
                    'security_flag' => (bool) $session->security_flag,
                    'poi_name' => $session->poi_name,
                    'poi_confidence' => $session->poi_confidence,
                    'watchlist_matches' => $session->watchlist_matches,
                    'camera' => $session->device?->name,
                    'role' => $session->device?->role,
                    'room' => [
                        'id' => $session->device?->room?->id,
                        'name' => $session->device?->room?->name,
                    ],
                    'room_area' => [
                        'id' => $session->device?->roomArea?->id,
                        'name' => $session->device?->roomArea?->name,
                    ],
                ]
            );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getCounters
     * <Function> Retrieves the page-header counters for the current filter
     *            scope (date range, device, search).
     *
     * <Notes> Entries/exits count sessions on devices tagged entry_point /
     *         exit_point (building-level enter/exit). Merged trackers are
     *         excluded from every counter. Security counts confirmed
     *         results only (security_flag) — watchlist evaluations without
     *         a match are not hits.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string,int>
     */
    public function getCounters(Request $request): array
    {
        $this->sweepStale();

        return [
            'in_now' => $this->applyFilters(CorsightPersonSession::query(), $request)
                ->countable()->in()->count(),
            'security' => $this->applyFilters(CorsightPersonSession::query(), $request)
                ->countable()
                ->where('security_flag', true)
                ->count(),
            'entries' => $this->applyFilters(CorsightPersonSession::query(), $request)
                ->countable()
                ->whereHas('device', fn(Builder $query) => $query->where('role', 'entry_point'))
                ->count(),
            'exits' => $this->applyFilters(CorsightPersonSession::query(), $request)
                ->countable()
                ->whereHas('device', fn(Builder $query) => $query->where('role', 'exit_point'))
                ->count(),
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getDevices
     * <Function> Retrieves the device list for the filter bar.
     *
     * @return array<int,array{id:string,name:string,role:string}>
     */
    public function getDevices(): array
    {
        return CorsightDevice::orderBy('name')
            ->get(['id', 'name', 'role'])
            ->map(fn(CorsightDevice $device) => [
                'id' => $device->id,
                'name' => $device->name,
                'role' => $device->role,
            ])
            ->all();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getDetail
     * <Function> Retrieves a single session with its diary trail (the
     *            appearance events recorded for the visit — the audit view).
     *
     * @param  \App\Models\CorsightPersonSession  $session
     * @return array
     */
    public function getDetail(CorsightPersonSession $session): array
    {
        $session->load('device.room:id,name', 'device.roomArea:id,name');

        $trail = CorsightDeviceData::where('event_type', 'appearance')
            ->where('context->appearance_data->appearance_id', $session->appearance_id)
            ->orderBy('received_at')
            ->get()
            ->map(
                fn(CorsightDeviceData $row) => [
                    'received_at' => CorsightDisplay::timestamp($row->received_at),
                    'trigger' => $row->context['trigger'] ?? null,
                    'event_id' => $row->event_id,
                ]
            );

        return [
            'id' => $session->id,
            'appearance_id' => $session->appearance_id,
            'camera_id' => $session->camera_id,
            'status' => $session->status,
            'enter_at' => CorsightDisplay::timestamp($session->enter_at),
            'exit_at' => CorsightDisplay::timestamp($session->exit_at),
            'dwell_seconds' => $session->dwell_seconds,
            'last_seen_at' => CorsightDisplay::timestamp($session->last_seen_at),
            'gender' => $session->gender,
            'age_group' => $session->age_group,
            'face_score' => $session->face_score,
            'image' => $session->image,
            'security_flag' => (bool) $session->security_flag,
            'watchlist_matches' => $session->watchlist_matches,
            'poi_id' => $session->poi_id,
            'poi_name' => $session->poi_name,
            'poi_confidence' => $session->poi_confidence,
            'liveness' => $session->liveness,
            'mask_outcome' => $session->mask_outcome,
            'event_count' => $session->event_count,
            'camera' => $session->device?->name,
            'role' => $session->device?->role,
            'room' => [
                'id' => $session->device?->room?->id,
                'name' => $session->device?->room?->name,
            ],
            'room_area' => [
                'id' => $session->device?->roomArea?->id,
                'name' => $session->device?->roomArea?->name,
            ],
            'trail' => $trail,
        ];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> sweepStale
     * <Function> Closes "in" sessions whose last_seen_at is older than the
     *            configured stale threshold (config:
     *            corsight.stale_session_minutes). Called from the read path
     *            so the UI self-heals even when the scheduler is not
     *            running, and from the maintenance command.
     *
     * @return int
     */
    public function sweepStale(): int
    {
        $threshold = now()->subMinutes((int) config('corsight.stale_session_minutes', 30));

        $count = CorsightPersonSession::query()
            ->where('status', 'in')
            ->where('last_seen_at', '<', $threshold)
            ->update(['status' => 'abandoned', 'updated_at' => now()]);

        if ($count > 0) {
            Log::warning('Corsight stale sessions closed.', ['count' => $count, 'threshold' => (string) $threshold]);
        }

        return $count;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> applyFilters
     * <Function> Applies the shared query filters (device, role scope,
     *            date range, security-only, in-now and free-text search).
     *
     * <Notes> Date ranges are entered as business dates (Asia/Manila) and
     *         enter_at is stored UTC, so the boundaries are converted to
     *         UTC instants instead of compared as UTC calendar days.
     *         Security-only keys on security_flag (confirmed results).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\CorsightPersonSession>  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\CorsightPersonSession>
     */
    private function applyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->query('device_id'), fn(Builder $query, $deviceId) => $query->where('device_id', $deviceId))
            ->when(
                $request->query('role'),
                fn(Builder $query, $role) => $query->whereHas('device', fn(Builder $device) => $device->where('role', $role))
            )
            ->when(
                $request->query('date_from'),
                fn(Builder $query, $date) => $query->where('enter_at', '>=', Carbon::parse($date, 'Asia/Manila')->startOfDay()->utc())
            )
            ->when(
                $request->query('date_to'),
                fn(Builder $query, $date) => $query->where('enter_at', '<', Carbon::parse($date, 'Asia/Manila')->startOfDay()->addDay()->utc())
            )
            ->when(
                $request->query('security_only'),
                fn(Builder $query) => $query->where('security_flag', true)
            )
            ->when($request->query('in_now'), fn(Builder $query) => $query->where('status', 'in'))
            ->when(
                $request->query('search'),
                fn(Builder $query, $search) => $query->where(
                    fn(Builder $query) => $query
                        ->where('appearance_id', 'LIKE', "%{$search}%")
                        ->orWhereHas('device', fn(Builder $device) => $device
                            ->where('name', 'LIKE', "%{$search}%")
                            ->orWhereHas('room', fn(Builder $room) => $room->where('name', 'LIKE', "%{$search}%")))
                )
            );
    }
}
