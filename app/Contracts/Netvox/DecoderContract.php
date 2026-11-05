<?php

namespace App\Contracts\Netvox;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class DecoderContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // ATTRIBUTES       (1.0)  The attribute list supported by the Netvox decoder.
    // PREFIXES         (2.0)  The data types that can be extracted from a
    //                         payload with a certain prefix.
    // dataTypes        (3.0)  The attributes that will be saved to the database.
    // $decoded         (4.0)  The decoded payload array.
    // decode           (5.0)  Decodes the given payload.
    // battery          (6.0)  Decodes the BATTERY data segment.
    // status           (7.0)  Decodes the STATUS data segment.
    // occupancy        (8.0)  Decodes the OCCUPANCY data segment.
    // temperature      (9.0)  Decodes the TEMPERATURE data segment.
    // humidity         (10.0) Decodes the HUMIDITY data segment.
    // carbon_dioxide   (11.0) Decodes the CARBON DIOXIDE data segment.
    // illuminance      (12.0) Decodes the ILLUMINANCE data segment.
    // ammonia          (13.0) Decodes the AMMONIA data segment.
    // wind_speed       (14.0) Decodes the WIND SPEED data segment.
    // noise            (15.0) Decodes the NOISE data segment.
    // fire             (16.0) Decodes the FIRE data segment.
    // hightemp         (17.0) Decodes the HIGHTEMP data segment.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> ATTRIBUTES
     * <Function> The attribute list supported by the Netvox decoder.
     *
     * @var array<string,int>
     */
    const ATTRIBUTES = [
        // <method> => <byte_count>

        'battery' => 1,
        'status' => 1,
        'occupancy' => 1,
        'temperature' => 2,
        'humidity' => 2,
        'carbon_dioxide' => 2,
        'illuminance' => 2,
        'ammonia' => 2,
        'wind_speed' => 2,
        'noise' => 2,
        'fire' => 1,
        'hightemp' => 1,
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> PREFIXES
     * <Function> The data types that can be extracted from a payload with a
     *            certain prefix.
     *
     * @var array<string,string[]>
     */
    const PREFIXES = [
        '014d01' => ['battery', 'status'],
        '010201' => ['battery', 'status'],
        '01050c' => ['battery', 'temperature', 'humidity', 'wind_speed'],
        '010507' => ['battery', 'carbon_dioxide', 'ammonia', 'noise'],
        '010301' => ['battery', 'temperature', 'illuminance', 'occupancy'],
        '010a01' => ['battery', 'fire', 'hightemp'],
        '016900' => ['battery', 'status'],
        '016901' => ['battery', 'status'],

    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> dataTypes
     * <Function> The attributes that will be saved to the database.
     *            If returns an empty array, save all attributes.
     *
     * @return string[]
     */
    abstract protected function dataTypes(): array;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $decoded
     * <Function> The decoded payload array.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $decoded;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> decode
     * <Function> Decodes the given payload.
     *
     * @param  string  $payload
     * @return \Illuminate\Support\Collection
     */
    public function decode(string $payload): Collection
    {
        $this->decoded = collect();

        $bytes = Str::of($payload)->split(2);

        $prefix = $bytes->splice(0, 3)->implode('');

        if (!array_key_exists($prefix, self::PREFIXES)) {
            throw new Exception("The prefix [$prefix] is not supported!");
        }

        $methods = self::PREFIXES[$prefix];

        foreach ($methods as $method) {

            $byteCount = self::ATTRIBUTES[$method];

            $segment = $bytes->splice(0, $byteCount);

            if (blank($this->dataTypes()) || in_array($method, $this->dataTypes())) {
                $this->$method($segment);
            }
        }

        return $this->decoded;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> battery
     * <Function> Decodes the BATTERY data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function battery(Collection $bytes): self
    {
        [$unit, $notation, $offset] = ['V', 1e-1, 128];

        $dec = hexdec($bytes->implode(''));
        $value = ($dec >= $offset) ? ($dec - $offset) : $dec;
        $value *= $notation;

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> status
     * <Function> Decodes the STATUS data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function status(Collection $bytes): self
    {
        $unit = null;

        $value = !!hexdec($bytes->implode(''));

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> occupancy
     * <Function> Decodes the OCCUPANCY data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function occupancy(Collection $bytes): self
    {
        $unit = null;

        $value = !!hexdec($bytes->implode(''));

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> temperature
     * <Function> Decodes the TEMPERATURE data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function temperature(Collection $bytes): self
    {
        [$unit, $notation, $offset] = ['°C', 1e-2, 32768];

        $dec = hexdec($bytes->implode(''));
        $value = ($dec >= $offset) ? ($dec - (2 * $offset)) : $dec;
        $value = round($value * $notation, 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> humidity
     * <Function> Decodes the HUMIDITY data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function humidity(Collection $bytes): self
    {
        [$unit, $notation, $disabled] = ['%', 1e-2, 65535];

        $value = hexdec($bytes->implode(''));

        if ($value !== $disabled) {
            $value = round($value * $notation, 2);
            $this->decoded->push([
                'type' => __FUNCTION__,
                'context' => compact('value', 'unit'),
            ]);
        }

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> carbon_dioxide
     * <Function> Decodes the CARBON DIOXIDE data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function carbon_dioxide(Collection $bytes): self
    {
        [$unit, $notation, $disabled] = ['ppm', 1e-1, 65535];

        $value = hexdec($bytes->implode(''));

        if ($value !== $disabled) {
            $value = round($value * $notation, 2);
            $this->decoded->push([
                'type' => __FUNCTION__,
                'context' => compact('value', 'unit'),
            ]);
        }

        return $this;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> illuminance
     * <Function> Decodes the ILLUMINANCE data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function illuminance(Collection $bytes): self
    {
        [$unit, $notation, $disabled] = ['lux', 1e0, 65535];

        $value = hexdec($bytes->implode(''));

        if ($value !== $disabled) {
            $value = round($value * $notation, 2);
            $this->decoded->push([
                'type' => __FUNCTION__,
                'context' => compact('value', 'unit'),
            ]);
        }

        return $this;
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> ammonia
     * <Function> Decodes the AMMONIA data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function ammonia(Collection $bytes): self
    {
        [$unit, $notation, $disabled] = ['ppm', 1e-1, 65535];

        $value = hexdec($bytes->implode(''));

        if ($value !== $disabled) {
            $value *= $notation;
            $this->decoded->push([
                'type' => __FUNCTION__,
                'context' => compact('value', 'unit'),
            ]);
        }

        return $this;
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> wind_speed
     * <Function> Decodes the WIND SPEED data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function wind_speed(Collection $bytes): self
    {
        [$unit, $notation, $disabled] = ['m/s', 1e-2, 65535];

        $value = hexdec($bytes->implode(''));

        if ($value !== $disabled) {
            $value *= $notation;
            $this->decoded->push([
                'type' => __FUNCTION__,
                'context' => compact('value', 'unit'),
            ]);
        }

        return $this;
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> noise
     * <Function> Decodes the NOISE data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function noise(Collection $bytes): self
    {
        [$unit, $notation, $disabled] = ['dB', 1e-1, 65535];

        $value = hexdec($bytes->implode(''));

        if ($value !== $disabled) {
            $value *= $notation;
            $this->decoded->push([
                'type' => __FUNCTION__,
                'context' => compact('value', 'unit'),
            ]);
        }

        return $this;
    }


    /**
     * <Layer number> (16.0)
     *
     * <Processing name> fire
     * <Function> Decodes the FIRE data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function fire(Collection $bytes): self
    {
        $unit = null;
        $value = !!hexdec($bytes->implode(''));

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> hightemp
     * <Function> Decodes the HIGHTEMP data segment.
     *
     * @param  \Illuminate\Support\Collection  $bytes
     * @return self
     */
    protected function hightemp(Collection $bytes): self
    {
        $unit = null;

        $value = !!hexdec($bytes->implode(''));

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);

        return $this;
    }
}
