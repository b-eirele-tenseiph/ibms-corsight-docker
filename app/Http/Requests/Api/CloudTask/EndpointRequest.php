<?php

namespace App\Http\Requests\Api\CloudTask;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;


class EndpointRequest extends FormRequest
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // authorize              (1.0) The default uplink handler for lorawan.
    // rules                  (2.0) The uplink handler for milesight decoded
    // job                    (3.0) Get the job from cloud task payload

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> job
     * <Function> Get the job from cloud task payload
     *
     */
    public function job()
    {
        // Decode payload
        $payload = base64_decode($this->getContent());

        // Convert payload to array
        $payloadArray = json_decode($payload, true);

        // Get job data from payload
        $jobData = $payloadArray['data']['command'] ?? null;

        if (!$jobData) {
            return null;
        }

        // Unserialize job
        $job = unserialize($jobData);

        return $job;
    }
}
