<?php

namespace App\Contracts\Lorawan;

use App\Exceptions\Lorawan\InvalidEncodeCommandException;
use App\Models\{DeviceLatestData, LorawanDevice, LorawanDeviceData};
use App\Structs\Modbus\ModbusRTUFrame;
use Illuminate\Support\Facades\Cache;

abstract class EncoderContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $PDU               (1.0)  The Protocol Data Unit (PDU) for the encoder.
    // $defaultPort       (2.0)  The default port for the encoder.
    // $context           (3.0)  The added context parameters for the encoder.
    // __construct        (4.0)  Create a new encoder instance.
    // methods            (5.0)  List all commands that can be sent to the
    //                           device.
    // encode             (6.0)  Return the encoded data for the given input.
    // data               (7.0)  Set the data to be sent to the API
    // turnOnS1           (8.0)  Payload to turn on switch 1.
    // turnOnS2           (9.0)  Payload to turn on switch 2.
    // turnOnS3           (10.0) Payload to turn on switch 3.
    // turnOffS1          (11.0) Payload to turn off switch 1.
    // turnOffS2          (12.0) Payload to turn off switch 2.
    // turnOffS3          (13.0) Payload to turn off switch 3.
    // fireMode           (14.0) Payload to set the siren device in fire mode.
    // emergencyMode      (15.0) Payload to set the siren device in emergency
    //                           mode.
    // burglarMode        (16.0) Payload to set the siren device in burglar
    //                           mode.
    // doorbellMode       (17.0) Payload to set the siren device in doorbell
    //                           mode.
    // muteMode           (18.0) Payload to set the siren device in mute mode.
    // powerOn            (19.0) Payload to power on the thermostat device.
    // powerOff           (20.0) Payload to power off the thermostat device.
    // changeTemperature  (21.0) Payload to change the thermostat set point.
    // fanSpeedOff        (22.0) Payload to set the fan speed to 0.
    // fanSpeed1          (23.0) Payload to set the fan speed to 1.
    // fanSpeed2          (24.0) Payload to set the fan speed to 2.
    // fanSpeed3          (25.0) Payload to set the fan speed to 3.
    // fanSpeedAuto       (26.0) Payload to set the fan speed to auto.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $PDU
     * <Function> The Protocol Data Unit (PDU) for the encoder.
     *
     * @var \App\Structs\Modbus\ModbusRTUFrame|null
     */
    protected ?ModbusRTUFrame $PDU;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $defaultPort
     * <Function> The default port for the encoder.
     *
     * @var int
     */
    protected int $defaultPort = 84;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $context
     * <Function> The added context parameters for the encoder.
     *
     * @var array
     */
    protected array $context = [];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> __construct
     * <Function> Create a new encoder instance.
     *
     * @var array
     */
    public function __construct(
        protected LorawanDevice $device,
    ) {}

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> methods
     * <Function> List all commands that can be sent to the device.
     *
     * @var array
     */
    abstract protected function methods(): array;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> encode
     * <Function> Return the encoded data for the given input.
     *
     * @param string|array $input
     * @var array
     */
    public function encode(string|array $input): array
    {
        $this->context = $input;
        $method = $input['command'] ?? null;

        throw_unless(
            method_exists($this, $method),
            InvalidEncodeCommandException::commandIsNotValid($method)
        );

        return $this->$method();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> data
     * <Function> Set the data to be sent to the API.
     *
     * @var array
     */
    protected function data(array $data): array
    {
        return [
            'payload' => $data['payload'],
            'port'    => $data['port'],
            'data'    => $data['data'] ?? [[
                'transmission' => LorawanDeviceData::TRANSMISSION_DOWNLINK,
                'type'         => $data['type'] ?? null,
                'context'      => $data['context'] ?? [],
                'received_at'  => $data['received_at'] ?? now(),
            ]],
        ];
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> turnOnS1
     * <Function> Payload to turn on switch 1.
     *
     * @var array
     */
    protected function turnOnS1(): array
    {
        return $this->data([
            'payload' => '0811ff',
            'port'    => 85,
            'type'    => 'switch_1',
            'context' => [
                'value' => 'high',
                'unit'  => null,
            ],
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> turnOnS2
     * <Function> Payload to turn on switch 2.
     *
     * @var array
     */
    protected function turnOnS2(): array
    {
        return $this->data([
            'payload' => '0822ff',
            'port'    => 85,
            'type'    => 'switch_2',
            'context' => [
                'value' => 'high',
                'unit'  => null,
            ],
        ]);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> turnOnS3
     * <Function> Payload to turn on switch 3.
     *
     * @var array
     */
    protected function turnOnS3(): array
    {
        return $this->data([
            'payload' => '0844ff',
            'port'    => 85,
            'type'    => 'switch_3',
            'context' => [
                'value' => 'high',
                'unit'  => null,
            ],
        ]);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> turnOffS1
     * <Function> Payload to turn off switch 1.
     *
     * @var array
     */
    protected function turnOffS1(): array
    {
        return  $this->data([
            'payload' => '0810ff',
            'port'    => 85,
            'type'    => 'switch_1',
            'context' => [
                'value' => 'low',
                'unit'  => null,
            ],
        ]);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> turnOffS2
     * <Function> Payload to turn off switch 2.
     *
     * @var array
     */
    protected function turnOffS2(): array
    {
        return  $this->data([
            'payload' => '0820ff',
            'port'    => 85,
            'type'    => 'switch_2',
            'context' => [
                'value' => 'low',
                'unit'  => null,
            ],
        ]);
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> turnOffS3
     * <Function> Payload to turn off switch 3.
     *
     * @var array
     */
    protected function turnOffS3(): array
    {
        return  $this->data([
            'payload' => '0840ff',
            'port'    => 85,
            'type'    => 'switch_3',
            'context' => [
                'value' => 'low',
                'unit'  => null,
            ],
        ]);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> fireMode
     * <Function> Payload to set the siren device in fire mode.
     *
     * @var array
     */
    protected function fireMode(): array
    {
        return $this->data([
            'payload' => '9069000207080000000000',
            'port'    => 7,
            'type'    => 'fire_mode'
        ]);
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> emergencyMode
     * <Function> Payload to set the siren device in emergency mode.
     *
     * @var array
     */
    protected function emergencyMode(): array
    {
        return  $this->data([
            'payload' => '9069010207080000000000',
            'port'    => 7,
            'type'    => 'emergency_mode'
        ]);
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> burglarMode
     * <Function> Payload to set the siren device in burglar mode.
     *
     * @var array
     */
    protected function burglarMode(): array
    {
        return  $this->data([
            'payload' => '9069020207080000000000',
            'port'    => 7,
            'type'    => 'burglar_mode'
        ]);
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> doorbellMode
     * <Function> Payload to set the siren device in doorbell mode.
     *
     * @var array
     */
    protected function doorbellMode(): array
    {
        return  $this->data([
            'payload' => '9069030207080000000000',
            'port'    => 7,
            'type'    => 'doorbell_mode'
        ]);
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> muteMode
     * <Function> Payload to set the siren device in mute mode.
     *
     * @var array
     */
    protected function muteMode(): array
    {
        return  $this->data([
            'payload' => '9069040000000000000000',
            'port'    => 7,
            'type'    => 'mute_mode'
        ]);
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> powerOn
     * <Function> Payload to power on the thermostat device.
     *
     * @var array
     */
    protected function powerOn(): array
    {
        $this->PDU->setFunctionCode('05')
            ->setAddress('0001')
            ->setData('0000');

        return $this->data(
            [
                'payload' => $this->PDU->serialize(),
                'port'    => $this->defaultPort,
                'type'    => 'slave_power',
                'context' => [
                    'value' => 'on',
                    'unit'  => null,
                ],
                'received_at' => now()->addMinute(),
            ]
        );
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> powerOff
     * <Function> Payload to power off the thermostat device.
     *
     * @var array
     */
    protected function powerOff(): array
    {
        $this->PDU->setFunctionCode('05')
            ->setAddress('0001')
            ->setData('FF00');

        return $this->data(
            [
                'payload' => $this->PDU->serialize(),
                'port'    => $this->defaultPort,
                'type'    => 'slave_power',
                'context' => [
                    'value' => 'off',
                    'unit'  => null,
                ],
                'received_at' => now()->addMinute(),
            ]
        );
    }

    /**
     * <Layer number> (21.0)
     *
     * <Processing name> changeTemperature
     * <Function> Payload to change the thermostat set point.
     *
     * @var array
     */
    protected function changeTemperature(): array
    {
        $new = intval(round($this->context['parameter']['new_setpoint'] * 10));

        $value = Cache::remember(
            "device:{$this->device->id}:base_setpoint",
            now()->addMinutes(30),
            fn() => DeviceLatestData::where('device_id', $this->device->id)
                ->value('context->base_setpoint->value') ?? 22
        );

        $base = intval(round($value * 10));

        $data = str_pad(substr(dechex($new - $base), -4), 4, '0', STR_PAD_LEFT);

        $this->PDU->setFunctionCode('06')
            ->setAddress('004B')
            ->setData($data);

        return $this->data([
            'payload'     => $this->PDU->serialize(),
            'port'        => $this->defaultPort,
            'type'        => 'current_setpoint',
            'context'     => [
                'value' => $this->context['parameter']['new_setpoint'],
                'unit'  => '°C',
            ],
            'received_at' => now()->addMinute(),
        ]);
    }

    /**
     * <Layer number> (22.0)
     *
     * <Processing name> fanSpeedOff
     * <Function> Payload to set the fan speed to 0.
     *
     * @var array
     */
    protected function fanSpeedOff(): array
    {
        $this->PDU->setFunctionCode('06')
            ->setAddress('0004')
            ->setData('0000');

        return $this->data(
            [
                'payload' => $this->PDU->serialize(),
                'port'    => $this->defaultPort,
                'type'    => 'fan_speed',
                'context' => [
                    'value' => 0,
                    'unit' => null,
                ],
                'received_at' => now()->addMinute(),
            ]
        );
    }

    /**
     * <Layer number> (23.0)
     *
     * <Processing name> fanSpeed1
     * <Function> Payload to set the fan speed to 1.
     *
     * @var array
     */
    protected function fanSpeed1(): array
    {
        $this->PDU->setFunctionCode('06')
            ->setAddress('0004')
            ->setData('0001');

        return $this->data(
            [
                'payload' => $this->PDU->serialize(),
                'port'    => $this->defaultPort,
                'type'    => 'fan_speed',
                'context' => [
                    'value' => 1,
                    'unit' => null,
                ],
                'received_at' => now()->addMinute(),
            ]
        );
    }

    /**
     * <Layer number> (24.0)
     *
     * <Processing name> fanSpeed2
     * <Function> Payload to set the fan speed to 2.
     *
     * @var array
     */
    protected function fanSpeed2(): array
    {
        $this->PDU->setFunctionCode('06')
            ->setAddress('0004')
            ->setData('0002');

        return $this->data(
            [
                'payload' => $this->PDU->serialize(),
                'port' => $this->defaultPort,
                'type'    => 'fan_speed',
                'context' => [
                    'value' => 2,
                    'unit' => null,
                ],
                'received_at' => now()->addMinute(),
            ]
        );
    }

    /**
     * <Layer number> (25.0)
     *
     * <Processing name> fanSpeed3
     * <Function> Payload to set the fan speed to 3.
     *
     * @var array
     */
    protected function fanSpeed3(): array
    {
        $this->PDU->setFunctionCode('06')
            ->setAddress('0004')
            ->setData('0003');

        return $this->data(
            [
                'payload' => $this->PDU->serialize(),
                'port' => $this->defaultPort,
                'type'    => 'fan_speed',
                'context' => [
                    'value' => 3,
                    'unit' => null,
                ],
                'received_at' => now()->addMinute(),
            ]
        );
    }

    /**
     * <Layer number> (26.0)
     *
     * <Processing name> fanSpeedAuto
     * <Function> Payload to set the fan speed to auto.
     *
     * @var array
     */
    protected function fanSpeedAuto(): array
    {
        $this->PDU->setFunctionCode('06')
            ->setAddress('0004')
            ->setData('0004');

        return $this->data(
            [
                'payload' => $this->PDU->serialize(),
                'port' => $this->defaultPort,
                'type'    => 'fan_speed',
                'context' => [
                    'value' => 'auto',
                    'unit' => null,
                ],
                'received_at' => now()->addMinute(),
            ]
        );
    }
}
