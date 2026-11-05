<?php

namespace App\Decoders\Milesight\EPM5500P;

use App\Contracts\Milesight\DecoderContract as MilesightDecoder;

class UC300 extends MilesightDecoder
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // dataTypes                    (1.0)  The data types that will be saved to the
    //                                     database.
    // rs485Channels                (2.0)  The modbus channels with their corresponding
    //                                     data type.
    // rs485_uplink                 (3.0)  Decodes the RS485 UPLINK data segment.
    // frequency                    (4.0)  Decode the device's frequency.
    // voltage_a                    (5.0)  Decode the device's current voltage a.
    // voltage_b                    (6.0)  Decode the device's current voltage b.
    // voltage_c                    (7.0)  Decode the device's current voltage c.
    // avg_voltage_phase            (8.0)  Decode the device's slave power.
    // voltage_ab                   (9.0)  Decode the device's current voltage ab.
    // voltage_bc                   (10.0) Decode the device's current voltage bc.
    // voltage_ca                   (11.0) Decode the device's current voltage ca.
    // avg_voltage_line             (12.0) Decode the device's current average voltage
    //                                     line.
    // electricity_a                (13.0) Decode the device's current electricity a.
    // electricity_b                (14.0) Decode the device's current electricity b.
    // electricity_c                (15.0) Decode the device's current electricity c.
    // total_current                (16.0) Decode the device's total current.
    // active_power_a               (17.0) Decode the device's current power a.
    // active_power_b               (18.0) Decode the device's current power b.
    // active_power_c               (19.0) Decode the device's current power c.
    // total_active_power           (20.0) Decode the device's totol active power.
    // power_factor_a               (21.0) Decode the device's current power factor a.
    // power_factor_b               (22.0) Decode the device's current power factor b.
    // power_factor_c               (23.0) Decode the device's current power factor c.
    // power_factor_total           (24.0) Decode the device's total power factor.
    // positive_active_energy       (25.0) Decode the device's current positive
    //                                     active energy.

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
            'frequency',
            'voltage_a',
            'voltage_b',
            'voltage_c',
            'avg_voltage_phase',
            'voltage_ab',
            'voltage_bc',
            'voltage_ca',
            'avg_voltage_line',
            'electricity_a',
            'electricity_b',
            'electricity_c',
            'total_electricity',
            'active_power_a',
            'active_power_b',
            'active_power_c',
            'total_active_power',
            'power_factor_a',
            'power_factor_b',
            'power_factor_c',
            'power_factor_total',
            'positive_active_energy',
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
            '00' => 'frequency',
            '01' => 'voltage_a',
            '02' => 'voltage_b',
            '03' => 'voltage_c',
            '04' => 'avg_voltage_phase',
            '05' => 'voltage_ab',
            '06' => 'voltage_bc',
            '07' => 'voltage_ca',
            '08' => 'avg_voltage_line',
            '09' => 'electricity_a',
            '0a' => 'electricity_b',
            '0b' => 'electricity_c',
            '0c' => 'total_electricity',
            '0d' => 'active_power_a',
            '0e' => 'active_power_b',
            '0f' => 'active_power_c',
            '10' => 'total_active_power',
            '11' => 'power_factor_a',
            '12' => 'power_factor_b',
            '13' => 'power_factor_c',
            '14' => 'power_factor_total',
            '15' => 'positive_active_energy',
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
     * <Processing name> frequency
     * <Function> Decode the device's frequency.
     *
     * @param  string $data
     * @return void
     */
    protected function frequency(string $data)
    {
        [$unit, $notation] = ['Hz', 0.01];

        $dec = hexdec($data);
        $value = round($dec * $notation, 2);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> voltage_a
     * <Function> Decode the device's current voltage a.
     *
     * @param  string $data
     * @return void
     */
    protected function voltage_a(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> voltage_b
     * <Function> Decode the device's current voltage b.
     *
     * @param  string $data
     * @return void
     */
    protected function voltage_b(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }
    /**
     * <Layer number> (7.0)
     *
     * <Processing name> voltage_c
     * <Function> Decode the device's current voltage c.
     *
     * @param  string $data
     * @return void
     */
    protected function voltage_c(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> avg_voltage_phase
     * <Function> Decode the device's current average voltage.
     *
     * @param  string $data
     * @return void
     */
    protected function avg_voltage_phase(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> voltage_ab
     * <Function> Decode the device's current voltage ab.
     *
     * @param  string $data
     * @return void
     */
    protected function voltage_ab(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> voltage_bc
     * <Function> Decode the device's current voltage bc.
     *
     * @param  string $data
     * @return void
     */
    protected function voltage_bc(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> voltage_ca
     * <Function> Decode the device's current voltage ca.
     *
     * @param  string $data
     * @return void
     */
    protected function voltage_ca(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> avg_voltage_line
     * <Function> Decode the device's current average voltage line.
     *
     * @param  string $data
     * @return void
     */
    protected function avg_voltage_line(string $data)
    {
        [$unit, $notation] = ['V', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> electricity_a
     * <Function> Decode the device's current electricity a.
     *
     * @param  string $data
     * @return void
     */
    protected function electricity_a(string $data)
    {
        [$unit, $notation] = ['A', 0.001];

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> electricity_b
     * <Function> Decode the device's current electricity b.
     *
     * @param  string $data
     * @return void
     */
    protected function electricity_b(string $data)
    {
        [$unit, $notation] = ['A', 0.001];

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> electricity_c
     * <Function> Decode the device's current electricity c.
     *
     * @param  string $data
     * @return void
     */
    protected function electricity_c(string $data)
    {
        [$unit, $notation] = ['A', 0.001];

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> total_current
     * <Function> Decode the device's total current.
     *
     * @param  string $data
     * @return void
     */
    protected function total_electricity(string $data)
    {
        [$unit, $notation] = ['A', 0.001];

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> active_power_a
     * <Function> Decode the device's current power a.
     *
     * @param  string $data
     * @return void
     */
    protected function active_power_a(string $data)
    {
        [$unit, $notation] = ['kW', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> active_power_b
     * <Function> Decode the device's current power b.
     *
     * @param  string $data
     * @return void
     */
    protected function active_power_b(string $data)
    {
        [$unit, $notation] = ['kW', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> active_power_c
     * <Function> Decode the device's current power c.
     *
     * @param  string $data
     * @return void
     */
    protected function active_power_c(string $data)
    {
        [$unit, $notation] = ['kW', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> total_active_power
     * <Function> Decode the device's totol active power.
     *
     * @param  string $data
     * @return void
     */
    protected function total_active_power(string $data)
    {
        [$unit, $notation] = ['kW', 0.1];

        $dec = hexdec($data);
        $value = round($dec * $notation, 1);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }

    /**
     * <Layer number> (21.0)
     *
     * <Processing name> power_factor_a
     * <Function> Decode the device's current power factor a.
     *
     * @param  string $data
     * @return void
     */
    protected function power_factor_a(string $data)
    {
        $notation = 0.001;

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value'),
        ]);
    }

    /**
     * <Layer number> (22.0)
     *
     * <Processing name> power_factor_b
     * <Function> Decode the device's current power factor b.
     *
     * @param  string $data
     * @return void
     */
    protected function power_factor_b(string $data)
    {
        $notation = 0.001;

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value'),
        ]);
    }

    /**
     * <Layer number> (23.0)
     *
     * <Processing name> power_factor_c
     * <Function> Decode the device's current power factor c.
     *
     * @param  string $data
     * @return void
     */
    protected function power_factor_c(string $data)
    {
        $notation = 0.001;

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value'),
        ]);
    }

    /**
     * <Layer number> (24.0)
     *
     * <Processing name> power_factor_total
     * <Function> Decode the device's total power factor.
     *
     * @param  string $data
     * @return void
     */
    protected function power_factor_total(string $data)
    {
        $notation = 0.001;

        $dec = hexdec($data);
        $value = round($dec * $notation, 3);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value'),
        ]);
    }

    /**
     * <Layer number> (25.0)
     *
     * <Processing name> positive_active_energy
     * <Function> Decode the device's current positive active energy.
     *
     * @param  string $data
     * @return void
     */
    protected function positive_active_energy(string $data)
    {
        [$unit, $notation] = ['kWh', 0.0001];

        $dec = hexdec($data);
        $value = round($dec * $notation, 4);

        $this->decoded->push([
            'type' => __FUNCTION__,
            'context' => compact('value', 'unit'),
        ]);
    }
}
