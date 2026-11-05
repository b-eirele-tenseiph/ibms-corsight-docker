<?php

namespace App\Services\Corsight;

use App\Models\CorsightDevice;
use App\Models\CorsightPersonSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PersonSessionService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // handleAppearance      (1.0)  Upsert the person session for an
    //                               appearance event (any trigger).
    // handleCameraStatus    (2.0)  Reconcile open sessions when a camera
    //                               stops analyzing.
    // createSession         (3.0)  Create a session from the first-seen
    //                               event of any trigger.
    // applyUpdate           (4.0)  Apply a non-closing event to an open
    //                               session.
    // closeSession          (5.0)  Close a session with a clean exit event.
    // swapPhoto             (6.0)  Replace the session photo when the new
    //                               crop scores higher.
    // projectWatchlists     (7.0)  Shape the raw watchlist array for storage.
    // mergeWatchlists       (8.0)  Merge incoming watchlist state into the
    //                               stored state (latest non-null per list).
    // cleanOutcome          (9.0)  Normalize a face-feature outcome to null
    //                               when Fortify did not determine it.
    // applyMatch            (10.0) Apply a payload's match_data (POI,
    //                               watchlists, security flag) to a session.
    // hasSecurityMatch      (11.0) Detect a confirmed security result in a
    //                               payload's match_data.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> handleAppearance
     * <Function> Upsert the person session for an appearance event.
     *
     * <Notes> The session is created on the first-seen event of a new
     *         appearance_id — any trigger (SSE has no replay, so the start
     *         event may be lost); utc_time_started repeats on every event,
     *         so the enter time stays accurate. A lone trigger 2 creates
     *         and closes the session in one step. Events for a session that
     *         is already closed are ignored (replay guard).
     *
     * @param  \App\Models\CorsightDevice  $device
     * @param  array  $payload  Raw appearance event.
     * @return void
     */
    public function handleAppearance(CorsightDevice $device, array $payload): void
    {
        $appearanceId = $payload['appearance_data']['appearance_id'] ?? null;

        if (blank($appearanceId)) {
            return;
        }

        DB::transaction(function () use ($device, $payload, $appearanceId) {
            $session = CorsightPersonSession::where('device_id', $device->id)
                ->where('appearance_id', $appearanceId)
                ->lockForUpdate()
                ->first();

            $trigger = (int) ($payload['trigger'] ?? 3);

            if (! $session) {
                $session = $this->createSession($device, $payload);

                if ($trigger === 2) {
                    $this->closeSession($session, $payload);
                }

                return;
            }

            if ($session->status !== 'in') {
                return;
            }

            if ($trigger === 2) {
                $this->closeSession($session, $payload);

                return;
            }

            $this->applyUpdate($session, $payload);
        });
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handleCameraStatus
     * <Function> Reconcile open sessions when a camera stops analyzing.
     *
     * <Notes> Any status other than 1 (active) means no further appearance
     *         events can come from this camera — including 11 (display mode,
     *         running without analyzing) — so all open sessions of the
     *         device are closed as abandoned with null dwell (the real end
     *         time is unknown).
     *
     * @param  \App\Models\CorsightDevice  $device
     * @param  array  $payload  Raw camera_status event.
     * @return void
     */
    public function handleCameraStatus(CorsightDevice $device, array $payload): void
    {
        $status = $payload['update_data']['status'] ?? null;

        if ($status === null || $status === 1) {
            return;
        }

        DB::transaction(function () use ($device) {
            CorsightPersonSession::where('device_id', $device->id)
                ->where('status', 'in')
                ->lockForUpdate()
                ->update(['status' => 'abandoned', 'updated_at' => now()]);
        });
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createSession
     * <Function> Create a session from the first-seen event (any trigger).
     *
     * @param  \App\Models\CorsightDevice  $device
     * @param  array  $payload
     * @return \App\Models\CorsightPersonSession
     */
    private function createSession(CorsightDevice $device, array $payload): CorsightPersonSession
    {
        $appearance = $payload['appearance_data'] ?? [];
        $match = $payload['match_data'] ?? [];

        $sentAt = $payload['msg_send_timestamp'] ?? time();

        $session = new CorsightPersonSession([
            'device_id' => $device->id,
            'appearance_id' => $appearance['appearance_id'],
            'camera_id' => $payload['camera_data']['camera_id'] ?? '',
            'status' => 'in',
            'enter_at' => Carbon::createFromTimestamp($appearance['utc_time_started'] ?? $sentAt),
            'last_seen_at' => Carbon::createFromTimestamp($sentAt),
            'gender' => $this->cleanOutcome($payload['face_features_data']['gender_outcome'] ?? null),
            'age_group' => $this->cleanOutcome($payload['face_features_data']['age_group_outcome'] ?? null),
            'liveness' => $this->cleanOutcome($payload['face_features_data']['liveness_outcome'] ?? null),
            'mask_outcome' => $this->cleanOutcome($payload['face_features_data']['mask_outcome'] ?? null),
            'poi_id' => ($match['poi_id'] ?? null) ?: null,
            'poi_name' => ($match['poi_display_name'] ?? null) ?: null,
            'poi_confidence' => $match['poi_confidence'] ?? null,
            'event_count' => 1,
            'first_event_id' => $payload['event_id'] ?? null,
            'last_event_id' => $payload['event_id'] ?? null,
            'merged_to_tracker' => $appearance['merged_to_tracker'] ?? null,
            'security_flag' => $this->hasSecurityMatch($match),
        ]);

        $this->swapPhoto($session, $payload['crop_data'] ?? []);

        $watchlists = $this->projectWatchlists($match['watchlists'] ?? []);

        if ($watchlists !== null) {
            $session->watchlist_matches = $watchlists;
        }

        $session->save();

        return $session;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> applyUpdate
     * <Function> Apply a non-closing event (trigger 0/1/3) to an open
     *            session.
     *
     * <Notes> Last-writer-wins with non-null values only: a null in a later
     *         event never clears a stored value, and a POI match, once set,
     *         sticks for the whole visit.
     *
     * @param  \App\Models\CorsightPersonSession  $session
     * @param  array  $payload
     * @return void
     */
    private function applyUpdate(CorsightPersonSession $session, array $payload): void
    {
        $features = $payload['face_features_data'] ?? [];
        $match = $payload['match_data'] ?? [];
        $appearance = $payload['appearance_data'] ?? [];

        $session->last_seen_at = Carbon::createFromTimestamp($payload['msg_send_timestamp'] ?? time());
        $session->event_count = ($session->event_count ?? 0) + 1;

        if (! empty($payload['event_id'])) {
            $session->last_event_id = $payload['event_id'];
        }

        $this->swapPhoto($session, $payload['crop_data'] ?? []);

        if ($gender = $this->cleanOutcome($features['gender_outcome'] ?? null)) {
            $session->gender = $gender;
        }

        if ($ageGroup = $this->cleanOutcome($features['age_group_outcome'] ?? null)) {
            $session->age_group = $ageGroup;
        }

        if ($liveness = $this->cleanOutcome($features['liveness_outcome'] ?? null)) {
            $session->liveness = $liveness;
        }

        if ($mask = $this->cleanOutcome($features['mask_outcome'] ?? null)) {
            $session->mask_outcome = $mask;
        }

        $this->applyMatch($session, $match);

        if (! empty($appearance['merged_to_tracker']) && $session->merged_to_tracker === null) {
            $session->merged_to_tracker = $appearance['merged_to_tracker'];
        }

        $session->save();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> closeSession
     * <Function> Close a session with a clean exit event (trigger 2).
     *
     * <Notes> The end event carries the best face of the whole appearance,
     *         so the photo swap runs once more before the status flips.
     *
     * @param  \App\Models\CorsightPersonSession  $session
     * @param  array  $payload
     * @return void
     */
    private function closeSession(CorsightPersonSession $session, array $payload): void
    {
        $sentAt = $payload['msg_send_timestamp'] ?? time();

        $this->swapPhoto($session, $payload['crop_data'] ?? []);

        // The end event carries the final match state of the visit — a POI
        // or watchlist match first seen here must not be lost.
        $this->applyMatch($session, $payload['match_data'] ?? []);

        $session->status = 'exited';
        $session->exit_at = Carbon::createFromTimestamp($sentAt);
        $session->last_seen_at = Carbon::createFromTimestamp($sentAt);
        $session->dwell_seconds = max(0, (int) round($sentAt - $session->enter_at->timestamp));
        $session->event_count = ($session->event_count ?? 0) + 1;

        if (! empty($payload['event_id'])) {
            $session->last_event_id = $payload['event_id'];
        }

        $session->save();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> swapPhoto
     * <Function> Replace the session photo when the new crop scores higher.
     *
     * @param  \App\Models\CorsightPersonSession  $session
     * @param  array  $crop  Raw crop_data of the event.
     * @return void
     */
    private function swapPhoto(CorsightPersonSession $session, array $crop): void
    {
        if (empty($crop['face_crop_img'])) {
            return;
        }

        $score = (float) ($crop['face_score'] ?? 0);

        if ($session->face_score === null || $score > (float) $session->face_score) {
            $session->image = $crop['face_crop_img'];
            $session->face_score = $score;
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> projectWatchlists
     * <Function> Shape the raw watchlist array for storage.
     *
     * @param  array  $watchlists
     * @return array|null  [{name, type, outcome, severity, repeated_alert}]
     */
    private function projectWatchlists(array $watchlists): ?array
    {
        $projected = [];

        foreach ($watchlists as $watchlist) {
            $projected[] = [
                'name' => $watchlist['display_name'] ?? null,
                'type' => $watchlist['watchlist_type'] ?? null,
                'outcome' => $watchlist['match_outcome'] ?? null,
                'severity' => $watchlist['severity'] ?? 0,
                'repeated_alert' => $watchlist['repeated_alert'] ?? false,
            ];
        }

        return $projected === [] ? null : $projected;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> mergeWatchlists
     * <Function> Merge incoming watchlist state into the stored state.
     *
     * <Notes> Latest non-null outcome per watchlist (matched by display
     *         name); a later event never produces two entries for the same
     *         list.
     *
     * @param  array|null  $stored
     * @param  array  $incoming
     * @return array
     */
    private function mergeWatchlists(?array $stored, array $incoming): array
    {
        $merged = [];

        foreach ($stored ?? [] as $entry) {
            if ($entry['name'] !== null) {
                $merged[$entry['name']] = $entry;
            }
        }

        foreach ($incoming as $entry) {
            if ($entry['name'] !== null) {
                $previous = $merged[$entry['name']] ?? null;

                $merged[$entry['name']] = [
                    'name' => $entry['name'],
                    'type' => $entry['type'] ?? $previous['type'] ?? null,
                    'outcome' => $entry['outcome'] ?? $previous['outcome'] ?? null,
                    'severity' => $entry['severity'] ?? 0,
                    'repeated_alert' => $entry['repeated_alert'] ?? false,
                ];
            }
        }

        return array_values($merged);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> cleanOutcome
     * <Function> Normalize a face-feature outcome to null when Fortify did
     *            not actually determine it.
     *
     * @param  string|null  $value
     * @return string|null
     */
    private function cleanOutcome(?string $value): ?string
    {
        if (in_array($value, ['not_determined', 'inconclusive'], true)) {
            return null;
        }

        return $value;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> applyMatch
     * <Function> Apply a payload's match_data (POI identity, watchlist
     *            state, security flag) to a session.
     *
     * <Notes> Last-writer-wins with non-null values only; a POI match,
     *         once set, sticks for the whole visit. Shared by the update
     *         path (trigger 0/1/3) and the close path (trigger 2), which
     *         carries the final match state of the appearance.
     *
     * @param  \App\Models\CorsightPersonSession  $session
     * @param  array  $match  Raw match_data of the event.
     * @return void
     */
    private function applyMatch(CorsightPersonSession $session, array $match): void
    {
        if (! empty($match['poi_id'])) {
            if ($session->poi_id === null) {
                $session->poi_id = $match['poi_id'];
            }

            if (! empty($match['poi_display_name'])) {
                $session->poi_name = $match['poi_display_name'];
            }

            if (($match['poi_confidence'] ?? null) !== null) {
                $session->poi_confidence = $match['poi_confidence'];
            }
        }

        $incoming = $this->projectWatchlists($match['watchlists'] ?? []);

        if ($incoming !== null) {
            $session->watchlist_matches = $this->mergeWatchlists($session->watchlist_matches, $incoming);
        }

        if (! $session->security_flag && $this->hasSecurityMatch($match)) {
            $session->security_flag = true;
        }
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> hasSecurityMatch
     * <Function> Detect a confirmed security result (POI match or a
     *            watchlist "matched" outcome) in a payload's match_data.
     *
     * <Notes> Evaluation-only outcomes ("not_determined") mean the camera
     *         checked and found nothing — they must never raise the
     *         security flag, or every visit would count as a hit.
     *
     * @param  array  $match  Raw match_data of the event.
     * @return bool
     */
    private function hasSecurityMatch(array $match): bool
    {
        if (! empty($match['poi_id'])) {
            return true;
        }

        foreach ($match['watchlists'] ?? [] as $watchlist) {
            if (($watchlist['match_outcome'] ?? null) === 'matched') {
                return true;
            }
        }

        return false;
    }
}
