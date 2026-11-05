<?php

namespace App\Structs\Modbus;

use Illuminate\Support\Str;

class ModbusRTUFrame
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $slaveID         (1.0)  The slave id of the slave device/unit.
    // $functionCode    (2.0)  The function code for the ModBus RTU.
    // $address         (3.0)  The register address of the RTU.
    // $data            (4.0)  The data or data count for the command.
    // $CRC             (5.0)  The Cyclic Redundancy Check for error handling.
    // $payload         (6.0)  The fully generated RTU command payload.
    // setSlaveID       (7.0)  Set the slave of the RTU.
    // setFunctionCode  (8.0)  Set the function code of the RTU.
    // setAddress       (9.0)  Set the register address of the RTU.
    // setData          (10.0) Set the data of the RTU.
    // serialize        (11.0) Generate the full RTU command payload including
    //                         CRC.
    // generateCRC      (12.0) Generate the CRC for the RTU command.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $slaveID
     * <Function> The slave id of the slave device/unit.
     *
     * @var string
     */
    private string $slaveID = 'FF';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $functionCode
     * <Function> The function code for the ModBus RTU.
     *
     * @var string
     */
    private string $functionCode = 'FF';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $address
     * <Function> The register address of the ModBus RTU.
     *
     * @var string
     */
    private string $address = 'FFFF';

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $data
     * <Function> The data or data count for the command.
     *
     * @var string
     */
    private string $data = 'FFFF';

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $CRC
     * <Function> The Cyclic Redundancy Check for error handling.
     *
     * @var string
     */
    private string $CRC;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $payload
     * <Function> The fully generated RTU command payload.
     *
     */
    private $payload;

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> setSlaveID
     * <Function> Set the slave of the RTU.
     *
     * @param string $hex
     *
     * @return self
     */
    public function setSlaveID(string $hex): self
    {
        $this->slaveID = $hex;

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> setFunctionCode
     * <Function> Set the function code of the RTU.
     *
     * @param string $hex
     *
     * @return self
     */
    public function setFunctionCode(string $hex): self
    {
        $this->functionCode = $hex;

        return $this;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> setAddress
     * <Function> Set the register address of the RTU.
     *
     * @param string $hex
     *
     * @return self
     */
    public function setAddress(string $hex): self
    {
        $this->address = $hex;

        return $this;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> setData
     * <Function> Set the data of the RTU.
     *
     * @param string $hex
     *
     * @return self
     */
    public function setData(string $hex): self
    {
        $this->data = $hex;

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> serialize
     * <Function> Generate the full RTU command payload including CRC.
     *
     * @return string
     */
    public function serialize(): string
    {
        // Ensure all components are properly formatted with 2 characters each
        $formattedSlaveID = str_pad($this->slaveID, 2, '0', STR_PAD_LEFT);
        $formattedFunctionCode = str_pad($this->functionCode, 2, '0', STR_PAD_LEFT);

        $this->payload = Str::of($formattedSlaveID)
            ->append($formattedFunctionCode)
            ->append($this->address)
            ->append($this->data);

        $this->generateCRC();

        return $this->payload->append($this->CRC)->upper()->__toString();
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> generateCRC
     * <Function> Generate the CRC for the RTU command.
     *
     * @return void
     */
    private function generateCRC(): void
    {
        $crc = 0xFFFF;

        foreach (str_split(hex2bin($this->payload)) as $char) {
            $crc ^= ord($char);
            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 1) ? ($crc >> 1) ^ 0xA001 : $crc >> 1;
            }
        }

        $this->CRC = sprintf('%02x%02x', $crc & 0xFF, ($crc >> 8) & 0xFF);
    }
}
