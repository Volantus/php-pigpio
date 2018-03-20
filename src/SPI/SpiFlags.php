<?php
namespace Volantus\Pigpio\SPI;

/**
 * Class SpiFlags
 *
 * @package Volantus\Pigpio\SPI
 */
class SpiFlags
{
    /**
     * @var int
     */
    private $flags = 0;

    /**
     * SpiFlags constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['mode'])) {
            $this->setMode($config['mode']);
        }

        if (isset($config['activeHigh'])) {
            $this->setActiveHigh($config['activeHigh']);
        }

        if (isset($config['notReserved'])) {
            $this->setNotReservedGpio($config['notReserved']);
        }

        if (isset($config['auxiliaryDevice']) && $config['auxiliaryDevice'] === true) {
            $this->flags |= 1 << 8;

            if (isset($config['mosiLeastSignificantFirst']) && $config['mosiLeastSignificantFirst'] == true) {
                $this->flags |= 1 << 14;
            }

            if (isset($config['misoLeastSignificantFirst']) && $config['misoLeastSignificantFirst'] == true) {
                $this->flags |= 1 << 15;
            }

            if (isset($config['wordSize'])) {
                $this->setWordSize($config['wordSize']);
            }
        }

        if (isset($config['threeWire']) && $config['threeWire'] === true) {
            $this->flags |= 1 << 9;

            if (isset($config['threeWireAlternatingCount'])) {
                $this->setThreeWireAlternatingByteCount($config['threeWireAlternatingCount']);
            }
        }
    }

    /**
     * @param int $mode
     */
    private function setMode(int $mode)
    {
        $this->flags |= $mode;
    }

    /**
     * @param array $pins
     */
    private function setActiveHigh(array $pins)
    {
        foreach ($pins as $pin) {
            $this->flags |= 1 << ($pin + 2);
        }
    }

    /**
     * @param array $pins
     */
    private function setNotReservedGpio(array $pins)
    {
        foreach ($pins as $pin) {
            $this->flags |= 1 << ($pin + 5);
        }
    }

    /**
     * @param int $count
     */
    private function setThreeWireAlternatingByteCount(int $count)
    {
        $this->flags |= $count << 10;
    }

    /**
     * @param int $size
     */
    private function setWordSize(int $size)
    {
        $this->flags |= $size << 16;
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }
}