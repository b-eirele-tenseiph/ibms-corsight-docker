<?php

namespace App\Decoders\Milesight\ADW300;

use App\Contracts\Milesight\DecoderContract as MilesightDecoder;

class UC300 extends MilesightDecoder
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // dataTypes            (1.0) The data types that will be saved to the
    //                            database.
    // rs485Channels        (2.0) The modbus channels with their corresponding
    //                            data type.
    // rs485_uplink         (3.0) Decodes the RS485 UPLINK data segment.
    // current_month        (4.0) Decode the device's fan speed.
    // last_month           (5.0) Decode the device's current setpoint.
    // last_2_months        (6.0) Decode the device's current temperature.
    // hexfloat             (7.0) converts the hex data into decimal.
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
            'current_month',
            'last_month',
            'last_2_months',
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
            '00' => 'current_month',
            '01' => 'last_month',
            '02' => 'last_2_months',
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
    protected function current_month(string $data)
    {
        $unit = 'kWh';
        $value = round($this->hexfloat($data), 2);
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
    protected function last_month(string $data)
    {
        $unit = 'kWh';
        $value = round($this->hexfloat($data), 2);
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
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
    protected function last_2_months(string $data)
    {
        $unit = 'kWh';
        $value = round($this->hexfloat($data), 2);
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }
    /**
     * <Layer number> (7.0)
     *
     * <Processing name> hexfloat
     * <Function> converts the hex to decimal.
     *
     * @param string $hex
     * @return float
     */
    function hexfloat(string $hex): float
    {
        $dec = hexdec($hex);
        if ($dec === 0) {
            return 0;
        }
        $sup = 1 << 23;
        $x = ($dec & ($sup - 1)) + $sup * ($dec >> 31 | 1);
        $exp = ($dec >> 23 & 0xFF) - 127;
        $sign = ($dec & 0x80000000) ? -1 : 1;
        return $sign * $x * pow(2, $exp - 23);
    }
}
