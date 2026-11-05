<?php

namespace App\Decoders\Milesight\RCF230CD;

use App\Contracts\Milesight\DecoderContract as MilesightDecoder;

class UC300 extends MilesightDecoder
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // dataTypes         (1.0) The data types that will be saved to the
    //                         database.
    // rs485Channels     (2.0) The modbus channels with their corresponding data
    //                         type.
    // rs485_uplink      (3.0) Decodes the RS485 UPLINK data segment.
    // fan_speed         (4.0) Decode the device's fan speed.
    // current_setpoint  (5.0) Decode the device's current setpoint.
    // temperature       (6.0) Decode the device's current temperature.
    // base_setpoint     (7.0) Decode the device's base setpoint.
    // slave_power       (8.0) Decode the device's slave power.
    /**
     * <Layer number> (1.0)
     *
     * <Processing name> dataTypes
     * <Function> The data types that will be saved to the database.
     *            If returns an empty array, save all data types.
     *
     * @return string[]
     */
    protected function dataTypes(): array
    {
        return [
            'fan_speed',
            'current_setpoint',
            'temperature',
            'base_setpoint',
            'slave_power'
        ];
    }
    /**
     * <Layer number> (2.0)
     *
     * <Processing name> rs485Channels
     * <Function> The modbus channels with their corresponding data type.
     *
     * @return array
     */
    protected function rs485Channels(): array
    {
        return [
            '00' => 'fan_speed',
            '01' => 'current_setpoint',
            '02' => 'temperature',
            '03' => 'base_setpoint',
            '04' => 'slave_power',
        ];
    }
    /**
     * <Layer number> (3.0)
     *
     * <Processing name> rs485_uplink
     * <Function> Decodes the RS485 UPLINK data segment.
     *
     * @return self
     */
    protected function rs485_uplink(): self
    {
        $channelID = $this->bytes->shift();
        $dataSize = hexdec($this->bytes->shift());
        $type = $this->bytes->shift();
        $data = $this->bytes->splice(0, $dataSize)->reverse()->implode('');
        if (array_key_exists($channelID, $this->rs485Channels())) {
            $method = $this->rs485Channels()[$channelID];
            $this->$method($data);
        }
        return $this;
    }
    /**
     * <Layer number> (4.0)
     *
     * <Processing name> fan_speed
     * <Function> Decode the device's fan speed.
     *
     * @param  string $data
     * @return void
     */
    protected function fan_speed(string $data)
    {
        $unit = null;
        $fanSpeedList = [
            '0000' => 0,
            '0001' => 1,
            '0002' => 2,
            '0003' => 3,
            '0004' => 'auto',
        ];
        $value = $fanSpeedList[$data];
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }
    /**
     * <Layer number> (5.0)
     *
     * <Processing name> current_setpoint
     * <Function> Decode the device's current setpoint.
     *
     * @param  string $data
     * @return void
     */
    protected function current_setpoint(string $data)
    {
        [$unit, $notation] = ['°C', 1e-1];
        $dec = hexdec($data);
        // handle invalid data when thermostat is off
        if ($dec <= 350 && $dec >= 50) {
            $value = round($dec * $notation, 1);
            $this->decoded->push([
                'type' => __FUNCTION__,
                'context' => compact('value', 'unit'),
            ]);
        }
    }
    /**
     * <Layer number> (6.0)
     *
     * <Processing name> temperature
     * <Function> Decode the device's current temperature.
     *
     * @param  string $data
     * @return void
     */
    protected function temperature(string $data)
    {
        [$unit, $notation] = ['°C', 1e-1];
        $value = round(hexdec($data) * $notation, 1);
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }
    /**
     * <Layer number> (7.0)
     *
     * <Processing name> base_setpoint
     * <Function> Decode the device's base setpoint.
     *
     * @param  string $data
     * @return void
     */
    protected function base_setpoint(string $data)
    {
        [$unit, $notation] = ['°C', 1e-1];
        $value = round(hexdec($data) * $notation, 1);
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }
    /**
     * <Layer number> (8.0)
     *
     * <Processing name> slave_power
     * <Function> Decode the device's slave power.
     *
     * @param  string $data
     * @return void
     */
    protected function slave_power(string $data)
    {
        $unit = null;
        // 1 means the modbus slave is shut down
        $value = !!hexdec($data) ? 'off' : 'on';
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }
}
