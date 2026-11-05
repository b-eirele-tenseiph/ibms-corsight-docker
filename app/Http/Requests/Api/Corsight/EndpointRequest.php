<?php

namespace App\Http\Requests\Api\Corsight;

use App\Models\CorsightAccount;
use App\Models\CorsightDevice;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class EndpointRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize           (1.0) Verify the X-Fortify-Signature HMAC over the
    //                           raw body against each active account secret.
    // rules               (2.0) Shows the required rules.
    // failedAuthorization (3.0) Reject an unsigned request with 401 JSON.
    // event               (4.0) Get the decoded Fortify event envelope.
    // eventId             (5.0) Get the Fortify event id from the envelope.
    // eventType           (6.0) Get the event type from the envelope.
    // cameraId            (7.0) Get the camera id from the envelope.
    // cameraDescription   (8.0) Get the camera label from the envelope.
    // device              (9.0) Resolve the iBMS device from the camera label.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if request is allowed.
     *
     * <Notes> Authorization is the X-Fortify-Signature HMAC over the raw
     *         body, keyed with one of the active accounts' endpoint
     *         secrets - not an authenticated session.
     *
     * @return bool
     */
    public function authorize()
    {
        $accounts = CorsightAccount::all();

        if ($accounts->isEmpty()) {
            Log::warning('Corsight webhook rejected: no account configured.');

            return false;
        }

        $provided = (string) $this->header('X-Fortify-Signature');

        $valid = $accounts->contains(fn(CorsightAccount $account) => hash_equals(
            hash_hmac('sha256', $this->getContent(), $account->endpoint_secret),
            $provided
        ));

        if (! $valid) {
            Log::warning('Corsight webhook rejected: invalid signature.', [
                'ip' => $this->ip(),
            ]);
        }

        return $valid;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> failedAuthorization
     * <Function> Abort a request that failed the signature check with a
     *            401 JSON response, matching the daemon/API contract
     *            (non-2xx triggers the daemon's retry queue).
     *
     * <Notes> The framework discards this method's return value, so the
     *         rejection must throw - returning a response does not
     *         abort the request.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw (new AuthorizationException('Invalid signature.'))
            ->withStatus(401)
            ->setResponse(Response::denyWithStatus(401, 'Invalid signature.'));
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> rules
     * <Function> Shows the required rules.
     *
     * <Notes> The body is a raw Fortify event envelope; individual event
     *         types carry different shapes, so no field rules are applied.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> event
     * <Function> Get the decoded Fortify event envelope as an array.
     *
     * @return array
     */
    public function event(): array
    {
        return (array) $this->json()->all();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> eventId
     * <Function> Get the Fortify event id from the envelope.
     *
     * @return string|null
     */
    public function eventId(): ?string
    {
        return $this->event()['event_id'] ?? null;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> eventType
     * <Function> Get the event type from the envelope.
     *
     * @return string|null
     */
    public function eventType(): ?string
    {
        return $this->event()['event_type'] ?? null;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> cameraId
     * <Function> Get the camera id from the envelope.
     *
     * @return string|null
     */
    public function cameraId(): ?string
    {
        return $this->event()['camera_data']['camera_id'] ?? null;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> cameraDescription
     * <Function> Get the camera label (Fortify camera_description) from the
     *            envelope. This is the operator-typed label the iBMS device
     *            name is matched against.
     *
     * @return string|null
     */
    public function cameraDescription(): ?string
    {
        $description = $this->event()['camera_data']['camera_description'] ?? null;

        return filled($description) ? trim((string) $description) : null;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> device
     * <Function> Resolve the iBMS device for this event by matching the
     *            camera label (Fortify camera_description) against the
     *            device name. Returns null when the label is absent or
     *            matches no device; the job drops such events.
     *
     * @return \App\Models\CorsightDevice|null
     */
    public function device(): ?CorsightDevice
    {
        $label = $this->cameraDescription();

        return $label
            ? CorsightDevice::where('name', $label)->first()
            : null;
    }
}
