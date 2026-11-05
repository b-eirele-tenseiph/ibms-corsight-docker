<?php

namespace App\Contracts\Acrel;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class DecoderContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $bytes		          (1.0) The array of the payload bytes.
    // $decoded	              (2.0) The decoded payload array.
    // $halves		          (3.0) The array of splitted decoded payload.
    // dataTypes	          (4.0) The data types that will be saved to the
    //                              database. If returns an empty array,
    //                              save all attributes.
    // prefixes	              (5.0) The supported data types keyed by their
    //                              prefix.
    // decode		          (6.0) Decodes the given payload.
    // PT		              (7.0) Return the value of PT.
    // CT		              (8.0) Return the value of CT.
    // computation	          (9.0) Computes if the $key is hexadecimal and
    //                              if true, converts to decimal
    // voltage_a	          (10.0) Computes the voltage A phase.
    // voltage_b	          (11.0) Computes the voltage B phase.
    // voltage_c	          (12.0) Computes the voltage C phase.
    // voltage_ab	          (13.0) Computes the voltage between A and B phase.
    // voltage_bc	          (14.0) Computes the voltage between B and C phase.
    // voltage_ca	          (15.0) Computes the voltage between C and A phase.
    // electricity_a	      (16.0) Computes the electricity A phase.
    // electricity_b	      (17.0) Computes the electricity B phase.
    // electricity_c	      (18.0) Computes the electricity C phase.
    // active_power_a         (19.0) Computes the active power A phase.
    // active_power_b         (20.0) Computes the active power B phase.
    // active_power_c         (21.0) Computes the active power C phase.
    // total_active_power     (22.0) Computes the total active power.
    // positive_active_energy (23.0) Computes the total positive active energy.
    // active_energy_a        (24.0) Computes the energy consumption of phase A.
    // active_energy_b        (25.0) Computes the energy consumption of phase B.
    // active_energy_c        (26.0) Computes the energy consumption of phase C.
    // power_factor_a         (27.0) Computes the power factor of phase A
    // power_factor_b         (28.0) Computes the power factor of phase B
    // power_factor_c         (29.0) Computes the power factor of phase C
    // power_factor_total     (30.0) Computes the total power factor of 3 phase.
    // frequency              (31.0) Fetch the latest frequency.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $bytes
     * <Function> The array of the payload bytes.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $bytes;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $decoded
     * <Function> The decoded payload array.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $decoded;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $halves
     * <Function> The array of splitted decoded payload.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $halves = [];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> dataTypes
     * <Function> The data types that will be saved to the database.
     *            If returns an empty array, save all attributes.
     *
     * @return string[]
     */
    abstract protected function dataTypes(): array;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> prefixes
     * <Function> The supported data types keyed by their prefix.
     *
     * @return string[]
     */
    protected function prefixes()
    {
        return [];
    }

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
        $this->decoded = collect();

        Str::of($payload)->split(10)->each(function ($segment) {
            $this->halves[hexdec(substr($segment, 0, 2))] = substr($segment, 2, 8);
        });

        collect($this->dataTypes())->each(function ($segment, $key) {

            $index = $this->prefixes()[$segment];
            $this->$segment($index);
        });

        return $this->decoded;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> PT
     * <Function> Return the value of PT.
     *
     * @return int
     */
    private function PT()
    {
        return hexdec(formatEndian($this->halves[31]));
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> CT
     * <Function> Return the value of CT.
     *
     * @return int
     */
    private function CT()
    {
        return hexdec(formatEndian($this->halves[32]));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> computation
     * <Function> Computes if the $key is hexadecimal and if true,
     *            converts to decimal
     *
     * @param mixed $key
     * @return int
     */
    private function computation($key): int
    {
        if (ctype_xdigit($this->halves[$key])) {
            return hexdec(formatEndian($this->halves[$key]));
        }
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> voltage_a
     * <Function> Computes the voltage A phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function voltage_a($key): self
    {
        [$unit, $notation] = ['V', 0.1];

        $value = hexdec(formatEndian($this->halves[$key]));

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->PT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> voltage_b
     * <Function> Computes the voltage B phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function voltage_b($key): self
    {
        [$unit, $notation] = ['V', 0.1];

        $value = hexdec(formatEndian($this->halves[$key]));

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->PT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> voltage_c
     * <Function> Computes the voltage C phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function voltage_c($key): self
    {
        [$unit, $notation] = ['V', 0.1];

        $value = hexdec(formatEndian($this->halves[$key]));

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->PT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> voltage_ab
     * <Function> Computes the voltage between A and B phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function voltage_ab($key): self
    {
        [$unit, $notation] = ['V', 0.1];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->PT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> voltage_bc
     * <Function> Computes the voltage between B and C phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function voltage_bc($key): self
    {
        [$unit, $notation] = ['V', 0.1];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->PT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> voltage_ca
     * <Function> Computes the voltage between C and A phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function voltage_ca($key): self
    {
        [$unit, $notation] = ['V', 0.1];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->PT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> electricity_a
     * <Function> Computes the electricity A phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function electricity_a($key): self
    {
        [$unit, $notation] = ['A', 0.01];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->CT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> electricity_b
     * <Function> Computes the electricity B phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function electricity_b($key): self
    {
        [$unit, $notation] = ['A', 0.01];

        $value = $this->computation($key);
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->CT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> electricity_c
     * <Function> Computes the electricity C phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function electricity_c($key): self
    {
        [$unit, $notation] = ['A', 0.01];

        $value = $this->computation($key);
        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $this->CT() * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> active_power_a
     * <Function> Computes the active power A phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function active_power_a($key): self
    {
        [$unit, $notation] = ['kW', 0.001];

        $packed = pack('V', $this->computation($key));
        $value = unpack('l', $packed)[1];

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 3),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> active_power_b
     * <Function> Computes the active power B phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function active_power_b($key): self
    {
        [$unit, $notation] = ['kW', 0.001];

        $packed = pack('V', $this->computation($key));
        $value = unpack('l', $packed)[1];

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 3),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (21.0)
     *
     * <Processing name> active_power_c
     * <Function> Computes the active power C phase.
     *
     * @param mixed $key
     * @return $this
     */
    protected function active_power_c($key): self
    {
        [$unit, $notation] = ['kW', 0.001];

        $packed = pack('V', $this->computation($key));
        $value = unpack('l', $packed)[1];

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 3),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (22.0)
     *
     * <Processing name> total_active_power
     * <Function> Computes the total active power.
     *
     * @param mixed $key
     * @return $this
     */
    protected function total_active_power($key): self
    {
        [$unit, $notation] = ['kW', 0.001];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 3),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (23.0)
     *
     * <Processing name> positive_active_energy
     * <Function> Computes the positive active energy
     *
     * @param mixed $key
     * @return $this
     */
    protected function positive_active_energy($key): self
    {
        [$unit, $notation] = ['kWh', 0.01];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (24.0)
     *
     * <Processing name> active_energy_a
     * <Function> Computes the positive active energy of phase A.
     *
     * @param mixed $key
     * @return $this
     */
    protected function active_energy_a($key): self
    {
        [$unit, $notation] = ['kWh', 0.01];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (25.0)
     *
     * <Processing name> active_energy_b
     * <Function> Computes the positive active energy of phase B.
     *
     * @param mixed $key
     * @return $this
     */
    protected function active_energy_b($key): self
    {
        [$unit, $notation] = ['kWh', 0.01];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (26.0)
     *
     * <Processing name> active_energy_c
     * <Function> Computes the positive active energy of phase C.
     *
     * @param mixed $key
     * @return $this
     */
    protected function active_energy_c($key): self
    {
        [$unit, $notation] = ['kWh', 0.01];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round(($value * $notation) * $this->PT() * $this->CT(), 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (27.0)
     *
     * <Processing name> power_factor_a
     * <Function> Computes the power factor of phase A
     *
     * @param mixed $key
     * @return $this
     */
    protected function power_factor_a($key): self
    {
        [$unit, $notation] = [null, 0.001];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (28.0)
     *
     * <Processing name> power_factor_b
     * <Function> Computes the power factor of phase B
     *
     * @param mixed $key
     * @return $this
     */
    protected function power_factor_b($key): self
    {
        [$unit, $notation] = [null, 0.001];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (29.0)
     *
     * <Processing name> power_factor_c
     * <Function> Computes the power factor of phase C
     *
     * @param mixed $key
     * @return $this
     */
    protected function power_factor_c($key): self
    {
        [$unit, $notation] = [null, 0.001];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (30.0)
     *
     * <Processing name> power_factor_total
     * <Function> Computes the total power factor of 3 phase
     *
     * @param mixed $key
     * @return $this
     */
    protected function power_factor_total($key): self
    {
        [$unit, $notation] = [null, 0.001];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }

    /**
     * <Layer number> (31.0)
     *
     * <Processing name> frequency
     * <Function> Fetch the latest frequency.
     *
     * @param mixed $key
     * @return $this
     */
    protected function frequency($key): self
    {
        [$unit, $notation] = ['Hz', 0.01];

        $value = $this->computation($key);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => [
                'value' => round($value * $notation, 2),
                'unit' => $unit,
            ],
        ]);

        return $this;
    }
}
