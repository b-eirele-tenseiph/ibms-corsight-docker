<?php

namespace App\Contracts\Lorawan;

use App\Models\LorawanDeviceData;
use App\Services\Lorawan\ThresholdAnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Exception;

abstract class PayloadContract
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $device                  (1.0) The LorawanDevice model instance.
    // $payload                 (2.0) Set a global variable for the payload of
    //                                the lorawan device.
    // $decoder                 (3.0) The decoder class namespace.
    // $decoded                 (4.0) The collection of decoded data from the
    //                                payload.
    // $dataCollection          (5.0) The collection of the new
    //                                LorawanDeviceData model instances.
    // buildDecoderClass        (6.0) Build the namespace of the decoder class.
    // decodePayload            (7.0) Decodes the given payload.
    // transformData            (8.0) Transform the decoded data into a usable
    //                                form.
    // analyzeThreshold         (9.0) Check for threshold.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $device
     * <Function> The LorawanDevice model instance.
     *
     * @var \App\Models\LorawanDevice
     */
    public $device;
    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $payload
     * <Function> Set a global variable for the payload of the lorawan device.
     *
     * @var string
     */
    public $payload;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $decoder
     * <Function> The decoder class namespace.
     *
     * @var string
     */
    public string $decoder;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $decoded
     * <Function> The collection of decoded data from the payload.
     *
     * @var \Illuminate\Support\Collection
     */
    public $decoded;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $dataCollection
     * <Function> The collection of the new LorawanDeviceData model instances.
     *
     * @var \Illuminate\Support\Collection
     */
    public $dataCollection;

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> $thresholdService
     * <Function> The threshold analyzer service to analyze if the payload
     *            depending on the set configuration.
     *
     * @var \App\Services\Lorawan\ThresholdAnalyzerService
     */
    private $thresholdService;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> buildDecoderClass
     * <Function> Build the namespace of the decoder class.
     *
     * @return self
     *
     * @throws \Exception
     */
    public function buildDecoderClass(): self
    {
        //RETAIN
        $decoder = Str::of($this->device->manufacturer->name)
            ->ucfirst()
            ->prepend('App\\Decoders\\')
            ->when(
                // when the device is 'UC300' it will check if it is electric meter or aircon based on the device name
                $this->device->model === 'UC300',
                function ($value) {
                    $deviceModel = $this->device->context['slave_model'];
                    return $value->append("\\{$deviceModel}");
                }
            )
            ->append("\\{$this->device->model}")
            ->__toString();

        if (!class_exists($decoder)) {
            throw new Exception("No decoder class [$decoder] found!");
        }

        $this->decoder = $decoder;

        return $this;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> decodePayload
     * <Function> Decodes the given payload.
     *
     * @return self
     */
    public function decodePayload(): self
    {
        // RETAIN
        $this->decoded = collect(app($this->decoder)->decode($this->payload));

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> transformData
     * <Function> Transform the decoded data into a more usable form.
     *
     * @return self
     */
    public function transformData(): self
    {
        $this->decoded->transform(fn($data) => [
            'transmission' => LorawanDeviceData::TRANSMISSION_UPLINK,
            'type' => $data['type'],
            'context' => $data['context'],
            'received_at' => now(),
        ]);
        return $this;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> analyzeThreshold
     * <Function> Check for threshold.
     *
     * @return self
     */
    public function analyzeThreshold(): self
    {
        //Retain
        (new ThresholdAnalyzerService)->analyze($this->device, $this->dataCollection);

        return  $this;
    }
}
