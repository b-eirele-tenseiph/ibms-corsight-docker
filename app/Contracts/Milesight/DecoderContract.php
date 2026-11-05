<?php

namespace App\Contracts\Milesight;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class DecoderContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // DATA_TYPE_PREFIX         (1.0)  The list of supported data types keyed by
    //                                 their prefix.
    // RS485_REGISTER_CODE      (2.0)  The list of supported registers keyed by
    //                                 their code.
    // dataTypes                (3.0)  The data types that will be saved to the
    //                                 database.
    // $bytes                   (4.0)  The array of the payload bytes.
    // $decoded                 (5.0)  The decoded payload array.
    // decode                   (6.0)  Decodes the given payload.
    // di1                      (7.0)  Decodes the DI1 (digital input 1) data
    //                                 segment.
    // voltage                  (8.0)  Decodes the VOLTAGE data segment.
    // di1_counter              (9.0)  Decodes the DI1 COUNTER (digital input 1)
    //                                 data segment.
    // di2                      (10.0) Decodes the DI2 (digital input 2) data
    //                                 segment.
    // active_power             (11.0) Decodes the ACTIVE POWER data segment.
    // di2_counter              (12.0) Decodes the DI2 COUNTER (digital input 2)
    //                                 data segment.
    // di3                      (13.0) Decodes the DI3 (digital input 3) data
    //                                 segment.
    // power_factor             (14.0) Decodes the POWER FACTOR data segment.
    // di3_counter              (15.0) Decodes the DI3 COUNTER (digital input 3)
    //                                 data segment.
    // di4                      (16.0) Decodes the DI4 (digital input 4) data
    //                                 segment.
    // power_consumption        (17.0) Decodes the POWER CONSUMPTION data 
    //                                 segment.
    // di4_counter              (18.0) Decodes the DI4 COUNTER (digital input 4)
    //                                 data segment.
    // do1                      (19.0) Decodes the DO1 (digital output 1) data
    //                                 segment.
    // total_current            (20.0) Decodes the TOTAL CURRENT data segment.
    // do2                      (21.0) Decodes the DO2 (digital output 2) data
    //                                 segment.
    // switch_status            (22.0) Decodes the SWITCH STATUS data segment.
    // pt100_i1                 (23.0) Decodes the PT100 (pt100 input 1) data
    //                                 segment.
    // pt100_i2                 (24.0) Decodes the PT100 (pt100 input 2) data
    //                                 segment.
    // ai1_current              (25.0) Decodes the AI1 (analog input 1 4-20mA) 
    //                                 data segment.
    // ai2_current              (26.0) Decodes the AI2 (analog input 2 4-20mA) 
    //                                 data segment.
    // ai1_voltage              (27.0) Decodes the AI1 (analog input 1 0-10V) 
    //                                 data segment.
    // ai2_voltage              (28.0) Decodes the AI2 (analog input 2 0-10V) 
    //                                 data segment.
    // protocol_version         (29.0) Decodes the PROTOCOL VERSION data 
    //                                 segment.
    // hardware_version         (30.0) Decodes the HARDWARE VERSION data 
    //                                 segment.
    // software_version         (31.0) Decodes the SOFTWARE VERSION data 
    //                                 segment.
    // power                    (32.0) Decodes the POWER data segment.
    // modbus_exception         (33.0) Decodes the MODBUS EXCEPTION data 
    //                                 segment.
    // serial_number            (34.0) Decodes the SERIAL NUMBER data segment.
    // rs485_uplink             (35.0) Decodes the RS485 UPLINK data segment.
    // people_counter           (36.0) Decodes the VS121 UPLINK data segment.
    // battery                  (37.0) Decodes the WS202 battery level.
    // status                   (38.0) Decodes the WS202 status 
    //                                 (Vacant or Occupied).
    // daylight                 (39.0) Decodes the WS202 daylight (Bright or 
    //                                 Dark).
    // periodic_line_counter    (40.0) Decodes VS121 line cross counter set in 
    //                                 periodic reporting.
    // accumulated_line_counter (41.0) Decodes VS121 line cross counter set in 
    //                                 accumulated reporting.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> DATA_TYPE_PREFIX
     * <Function> The list of supported data types keyed by their prefix.
     *
     * @var array<string,string>
     */
    const DATA_TYPE_PREFIX = [
        '0300' => 'status',
        '0374' => 'voltage',
        '03c8' => 'di1_counter',
        '0400' => 'daylight',
        '0480' => 'active_power',
        '04c8' => 'di2_counter',
        '0500' => 'di3',
        '0581' => 'power_factor',
        '05c8' => 'di3_counter',
        '0600' => 'di4',
        '0683' => 'power_consumption',
        '06c8' => 'di4_counter',
        '0701' => 'do1',
        '07c9' => 'total_current',
        '0801' => 'do2',
        '0829' => 'switch_status',
        '0967' => 'pt100_i1',
        '0a67' => 'pt100_i2',
        '0b02' => 'ai1_current',
        '0c02' => 'ai2_current',
        '0d02' => 'ai1_voltage',
        '0e02' => 'ai2_voltage',
        'ff01' => 'protocol_version',
        'ff09' => 'hardware_version',
        'ff0a' => 'software_version',
        'ff0b' => 'power',
        'ff15' => 'modbus_exception',
        'ff16' => 'serial_number',
        'ff19' => 'rs485_uplink',
        '04c9' => 'people_counter',
        '05cc' => 'periodic_line_counter',
        '0dcc' => 'accumulated_line_counter',
        '0175' => 'battery',
    ];


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> RS485_REGISTER_CODE
     * <Function> The list of supported registers keyed by their code.
     *
     * @var array<string,string>
     */
    const RS485_REGISTER_CODE = [
        '00' => 'coil_register',
        '01' => 'discrete_register',
        '02' => 'input_register_int16',
        '03' => 'holding_register_int16',
        '04' => 'holding_register_int32',
        '05' => 'holding_register_float',
        '06' => 'input_register_int32',
        '07' => 'input_register_float',
        '08' => 'input_register_int32_with_upper_16bits',
        '09' => 'input_register_int32_with_lower_16bits',
        '0a' => 'holding_register_int32_with_upper_16bits',
        '0b' => 'holding_register_int32_with_lower_16bits',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> dataTypes
     * <Function> The data types that will be saved to the database.
     *            If returns an empty array, save all attributes.
     *
     * @return string[]
     */
    abstract protected function dataTypes(): array;

    protected function rs485Channels(): array
    {
        return [];
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $bytes
     * <Function> The array of the payload bytes.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $bytes;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $decoded
     * <Function> The decoded payload array.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $decoded;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> decode
     * <Function> Decodes the given payload.
     *
     * @param  string  $payload
     * @return \Illuminate\Support\Collection
     */
    public function decode(string $payload): Collection
    {
        // initialize the decoded collection
        $this->decoded = collect();

        // split the payload hexstring to an array of bytes
        $this->bytes = Str::of($payload)->split(2);

        // parse each data segment until the payload is empty
        while ($this->bytes->isNotEmpty()) {
            // the first 2 bytes of the segment can be used to determine which
            // data type it represents
            $prefix = $this->bytes->splice(0, 2)->implode('');



            // throw an exception when the prefix is not yet supported
            if (!array_key_exists($prefix, self::DATA_TYPE_PREFIX)) {
                /*
                |  Should reporting be needed, this line should be uncommented
                |  For now, not supported prefixes should be ignored and skipped.
                |
                |  throw new Exception("The prefix [$prefix] is not yet supported!");
                */
                continue;
            }

            // get the data type of the segment
            $method = self::DATA_TYPE_PREFIX[$prefix];

            // call the method that parses the data type
            $this->$method();
        }

        // filter the decoded array to only retrieve the data types that will be
        // persisted in the database
        $this->decoded = $this->decoded->when(filled($this->dataTypes()), fn($d) => $d->filter(
            fn($item) => in_array($item['type'], $this->dataTypes())
        ));

        // return the decoded payload
        return $this->decoded;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> di1
     * <Function> Decodes the DI1 (digital input 1) data segment.
     *
     * @return self
     */
    protected function di1(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = !!hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> voltage
     * <Function> Decodes the VOLTAGE data segment.
     *
     * @return self
     */
    protected function voltage(): self
    {
        [$length, $unit, $notation] = [2, 'V', 1e-1];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment) * $notation;

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> di1_counter
     * <Function> Decodes the DI1 COUNTER (digital input 1) data segment.
     *
     * @return self
     */
    protected function di1_counter(): self
    {
        [$length, $unit] = [4, 'pulses'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> di2
     * <Function> Decodes the DI2 (digital input 2) data segment.
     *
     * @return self
     */
    protected function di2(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = !!hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> active_power
     * <Function> Decodes the ACTIVE POWER data segment.
     *
     * @return self
     */
    protected function active_power(): self
    {
        [$length, $unit] = [2, 'W'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> di2_counter
     * <Function> Decodes the DI2 COUNTER (digital input 2) data segment.
     *
     * @return self
     */
    protected function di2_counter(): self
    {
        [$length, $unit] = [4, 'pulses'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> di3
     * <Function> Decodes the DI3 (digital input 3) data segment.
     *
     * @return self
     */
    protected function di3(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = !!hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> power_factor
     * <Function> Decodes the POWER FACTOR data segment.
     *
     * @return self
     */
    protected function power_factor(): self
    {
        [$length, $unit] = [1, '%'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> di3_counter
     * <Function> Decodes the DI3 COUNTER (digital input 3) data segment.
     *
     * @return self
     */
    protected function di3_counter(): self
    {
        [$length, $unit] = [4, 'pulses'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> di4
     * <Function> Decodes the DI4 (digital input 4) data segment.
     *
     * @return self
     */
    protected function di4(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = !!hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> power_consumption
     * <Function> Decodes the POWER CONSUMPTION data segment.
     *
     * @return self
     */
    protected function power_consumption(): self
    {
        [$length, $unit, $notation] = [4, 'kWh', 1e-3];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = round(hexdec($segment) * $notation, 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> di4_counter
     * <Function> Decodes the DI4 COUNTER (digital input 4) data segment.
     *
     * @return self
     */
    protected function di4_counter(): self
    {
        [$length, $unit] = [4, 'pulses'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> do1
     * <Function> Decodes the DO1 (digital output 1) data segment.
     *
     * @return self
     */
    protected function do1(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = !!hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> total_current
     * <Function> Decodes the TOTAL CURRENT data segment.
     *
     * @return self
     */
    protected function total_current(): self
    {
        [$length, $unit] = [2, 'mA'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (21.0)
     *
     * <Processing name> do2
     * <Function> Decodes the DO2 (digital output 2) data segment.
     *
     * @return self
     */
    protected function do2(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = !!hexdec($segment);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (22.0)
     *
     * <Processing name> switch_status
     * <Function> Decodes the SWITCH STATUS data segment.
     *
     * @return self
     */
    protected function switch_status(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->implode('');
        $dec = hexdec($segment);

        // assign bit checker variables
        [$s1Status, $s2Status, $s3Status] = [1, 2, 4];

        // save the status of switch_1
        $this->decoded->push([
            'type' => 'switch_1',
            'context' => [
                'value' => ($dec & $s1Status) ? 'high' : 'low',
                'unit' => $unit,
            ],
        ]);

        // save the status of switch_2
        $this->decoded->push([
            'type' => 'switch_2',
            'context' => [
                'value' => ($dec & $s2Status) ? 'high' : 'low',
                'unit' => $unit,
            ],
        ]);

        // save the status of switch_3
        $this->decoded->push([
            'type' => 'switch_3',
            'context' => [
                'value' => ($dec & $s3Status) ? 'high' : 'low',
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (23.0)
     *
     * <Processing name> pt100_i1
     * <Function> Decodes the PT100 (pt100 input 1) data segment.
     *
     * @return self
     */
    protected function pt100_i1(): self
    {
        [$length, $unit, $notation] = [2, '°C', 1e-1];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = round(hexdec($segment) * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (24.0)
     *
     * <Processing name> pt100_i2
     * <Function> Decodes the PT100 (pt100 input 2) data segment.
     *
     * @return self
     */
    protected function pt100_i2(): self
    {
        [$length, $unit, $notation] = [2, '°C', 1e-1];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = round(hexdec($segment) * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (25.0)
     *
     * <Processing name> ai1_current
     * <Function> Decodes the AI1 (analog input 1 4-20mA) data segment.
     *
     * @return self
     */
    protected function ai1_current(): self
    {
        [$length, $unit, $notation] = [4, 'mA', 1e-2];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = round(hexdec($segment) * $notation, 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (26.0)
     *
     * <Processing name> ai2_current
     * <Function> Decodes the AI2 (analog input 2 4-20mA) data segment.
     *
     * @return self
     */
    protected function ai2_current(): self
    {
        [$length, $unit, $notation] = [4, 'mA', 1e-2];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = round(hexdec($segment) * $notation, 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (27.0)
     *
     * <Processing name> ai1_voltage
     * <Function> Decodes the AI1 (analog input 1 0-10V) data segment.
     *
     * @return self
     */
    protected function ai1_voltage(): self
    {
        [$length, $unit, $notation] = [4, 'V', 1e-2];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = round(hexdec($segment) * $notation, 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (28.0)
     *
     * <Processing name> ai2_voltage
     * <Function> Decodes the AI2 (analog input 2 0-10V) data segment.
     *
     * @return self
     */
    protected function ai2_voltage(): self
    {
        [$length, $unit, $notation] = [4, 'V', 1e-2];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = round(hexdec($segment) * $notation, 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (29.0)
     *
     * <Processing name> protocol_version
     * <Function> Decodes the PROTOCOL VERSION data segment.
     *
     * @return self
     */
    protected function protocol_version(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->implode('');
        $value = 'V' . (int) $segment;

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (30.0)
     *
     * <Processing name> hardware_version
     * <Function> Decodes the HARDWARE VERSION data segment.
     *
     * @return self
     */
    protected function hardware_version(): self
    {
        [$length, $unit] = [2, null];
        $value = round($this->bytes->splice(0, $length)->implode('.'), 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (31.0)
     *
     * <Processing name> software_version
     * <Function> Decodes the SOFTWARE VERSION data segment.
     *
     * @return self
     */
    protected function software_version(): self
    {
        [$length, $unit] = [2, null];
        $value = round($this->bytes->splice(0, $length)->implode('.'), 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (32.0)
     *
     * <Processing name> power
     * <Function> Decodes the POWER data segment.
     *
     * @return self
     */
    protected function power(): self
    {
        [$length, $unit] = [1, null];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = 'on';

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (33.0)
     *
     * <Processing name> modbus_exception
     * <Function> Decodes the MODBUS EXCEPTION data segment.
     *
     * @return self
     */
    protected function modbus_exception(): self
    {
        [$length, $unit] = [1, 'channel'];
        $segment = $this->bytes->splice(0, $length)->reverse()->implode('');
        $value = hexdec($segment) + 1;

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (34.0)
     *
     * <Processing name> serial_number
     * <Function> Decodes the SERIAL NUMBER data segment.
     *
     * @return self
     */
    protected function serial_number(): self
    {
        [$length, $unit] = [8, null];
        $value = $this->bytes->splice(0, $length)->implode('');

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (35.0)
     *
     * <Processing name> rs485_uplink
     * <Function> Deodes the RS485 UPLINK data segment.
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function rs485_uplink(): self
    {
        $channelID = $this->bytes->shift();
        $dataSize = hexdec($this->bytes->shift());
        $data = $this->bytes->splice(0, $dataSize)->reverse()->implode('');

        if (!array_key_exists($channelID, $this->rs485Channels())) {
            throw new Exception("The RS485 channel [$channelID] is not supported!");
        };

        $method = $this->rs485Channels()[$channelID];

        $this->$method($data);

        return $this;
    }

    /**
     * <Layer number> (36.0)
     *
     * <Processing name> people_counter
     * <Function> Decodes the VS121 UPLINK data segment.
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function people_counter(): self
    {
        [$length, $unit] = [4, null];
        $value = hexdec($this->bytes->splice(0, $length)[0]);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (37.0)
     *
     * <Processing name> battery
     * <Function> Decodes the WS202 battery level.
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function battery(): self
    {
        [$length, $unit] = [1, '%'];

        $value = hexdec($this->bytes->splice(0, $length)[0]);


        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => $value,
                'unit'  => $unit
            ]
        ]);
        return $this;
    }

    /**
     * <Layer number> (38.0)
     *
     * <Processing name> status
     * <Function> Decodes the WS202 status (Vacant or Occupied).
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function status(): self
    {
        [$length, $unit] = [1, null];
        $value = $this->bytes->splice(0, $length)[0] === "01";

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => $value,
                'unit'  => $unit
            ]
        ]);
        return $this;
    }

    /**
     * <Layer number> (39.0)
     *
     * <Processing name> daylight
     * <Function> Decodes the WS202 daylight (Bright or Dark).
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function daylight(): self
    {
        [$length, $unit] = [1, null];

        $value = hexdec($this->bytes->splice(0, $length)[0]) === "01";

        $this->decoded->push([
            'type' => 'daylight',
            'context' => [
                'value' => $value,
                'unit'  => $unit
            ]
        ]);

        return $this;
    }

    /**
     * <Layer number> (40.0)
     *
     * <Processing name> periodic_line_counter
     * <Function> Decodes VS121 line cross counter set in periodic reporting.
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function periodic_line_counter(): self
    {
        [$length, $unit] = [2, null];
        $value = hexdec(
            $this->bytes
                ->splice(0, $length) // parse data from collection
                ->reverse()          // reverse for little endian
                ->implode('')        // combine
        );

        $this->decoded->push([
            'type' => 'periodic_in',
            'context' => compact('value', 'unit'),
        ]);


        [$length, $unit] = [2, null];
        $value = hexdec(
            $this->bytes
                ->splice(0, $length) // parse data from collection
                ->reverse()          // reverse for little endian
                ->implode('')        // combine
        );

        $this->decoded->push([
            'type' => 'periodic_out',
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (41.0)
     *
     * <Processing name> accumulated_line_counter
     * <Function> Decodes VS121 line cross counter set in accumulated reporting.
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function accumulated_line_counter()
    {
        [$length, $unit] = [2, null];
        $value = hexdec(
            $this->bytes
                ->splice(0, $length) // parse data from collection
                ->reverse()          // reverse for little endian
                ->implode('')        // combine
        );

        $this->decoded->push([
            'type' => 'accumulated_in',
            'context' => compact('value', 'unit'),
        ]);

        [$length, $unit] = [2, null];
        $value = hexdec(
            $this->bytes
                ->splice(0, $length) // parse data from collection
                ->reverse()          // reverse for little endian
                ->implode('')        // combine
        );

        $this->decoded->push([
            'type' => 'accumulated_out',
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }
}
