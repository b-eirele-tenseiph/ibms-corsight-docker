<?php

namespace App\Services\Corsight;

class CorsightEventProjector
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // project               (1.0)  Project a raw Fortify event into the slim
    //                               diary context; null when the event type
    //                               has no diary projection.
    // projectAppearance     (2.0)  Project an appearance event (audit trail
    //                               without the face crop or the biometric
    //                               embedding).
    // projectCameraStatus   (3.0)  Project a camera_status event (status +
    //                               time_active only, no manager telemetry).

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> project
     * <Function> Project a raw Fortify event into the slim diary context.
     *
     * <Notes> Single source of truth for what the slim event diary stores.
     *         Appearance and camera_status events are projected; every other
     *         event type returns null and is not stored (the registered-
     *         device hard gate already drops the camera-less ones upstream).
     *
     * @param  array  $payload  Raw Fortify event envelope.
     * @return array|null  Projected context, or null when not stored.
     */
    public function project(array $payload): ?array
    {
        return match ($payload['event_type'] ?? null) {
            'appearance' => $this->projectAppearance($payload),
            'camera_status' => $this->projectCameraStatus($payload),
            default => null,
        };
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> projectAppearance
     * <Function> Project an appearance event into the slim diary context.
     *
     * <Notes> Drops face_crop_img (the photo lives on the person session),
     *         signature_payload (biometric template — never stored) and all
     *         frame/zone/telemetry data.
     *
     * @param  array  $payload
     * @return array
     */
    private function projectAppearance(array $payload): array
    {
        $watchlists = [];

        foreach ($payload['match_data']['watchlists'] ?? [] as $watchlist) {
            $watchlists[] = [
                'display_name' => $watchlist['display_name'] ?? null,
                'watchlist_type' => $watchlist['watchlist_type'] ?? null,
                'match_outcome' => $watchlist['match_outcome'] ?? null,
                'severity' => $watchlist['severity'] ?? 0,
                'repeated_alert' => $watchlist['repeated_alert'] ?? false,
            ];
        }

        return [
            'event_id' => $payload['event_id'] ?? null,
            'event_type' => 'appearance',
            'msg_send_timestamp' => $payload['msg_send_timestamp'] ?? null,
            'trigger' => $payload['trigger'] ?? null,
            'camera_data' => [
                'camera_id' => $payload['camera_data']['camera_id'] ?? null,
                'camera_description' => $payload['camera_data']['camera_description'] ?? null,
                'stream_id' => $payload['camera_data']['stream_id'] ?? null,
            ],
            'appearance_data' => [
                'appearance_id' => $payload['appearance_data']['appearance_id'] ?? null,
                'utc_time_started' => $payload['appearance_data']['utc_time_started'] ?? null,
                'first_frame_id' => $payload['appearance_data']['first_frame_id'] ?? null,
                'merged_to_tracker' => $payload['appearance_data']['merged_to_tracker'] ?? null,
            ],
            'face_features_data' => [
                'gender_outcome' => $payload['face_features_data']['gender_outcome'] ?? null,
                'age_group_outcome' => $payload['face_features_data']['age_group_outcome'] ?? null,
                'liveness_outcome' => $payload['face_features_data']['liveness_outcome'] ?? null,
                'mask_outcome' => $payload['face_features_data']['mask_outcome'] ?? null,
            ],
            'crop_data' => [
                'face_score' => $payload['crop_data']['face_score'] ?? null,
            ],
            'match_data' => [
                'poi_id' => $payload['match_data']['poi_id'] ?? null,
                'poi_display_name' => $payload['match_data']['poi_display_name'] ?? null,
                'poi_confidence' => $payload['match_data']['poi_confidence'] ?? null,
                'watchlists' => $watchlists,
            ],
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> projectCameraStatus
     * <Function> Project a camera_status event into the slim diary context.
     *
     * <Notes> Keeps the status code, its label and the time-active metric
     *         (camera health); drops the manager telemetry that is only
     *         useful for Fortify support tickets.
     *
     * @param  array  $payload
     * @return array
     */
    private function projectCameraStatus(array $payload): array
    {
        return [
            'event_id' => $payload['event_id'] ?? null,
            'event_type' => 'camera_status',
            'msg_send_timestamp' => $payload['msg_send_timestamp'] ?? null,
            'camera_data' => [
                'camera_id' => $payload['camera_data']['camera_id'] ?? null,
                'camera_description' => $payload['camera_data']['camera_description'] ?? null,
                'stream_id' => $payload['camera_data']['stream_id'] ?? null,
            ],
            'update_data' => [
                'status' => $payload['update_data']['status'] ?? null,
                'status_str' => $payload['update_data']['status_str'] ?? null,
                'time_active' => $payload['update_data']['time_active'] ?? null,
            ],
        ];
    }
}
