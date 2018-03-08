<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Class Bitmap
 *
 * @package Volantus\Pigpio\Protocol
 */
class Bitmap
{
    /**
     * @var array
     */
    private $pins;

    /**
     * Bitmap constructor.
     *
     * @param array $pins GPIP pins e.g. [16, 20, 21]
     */
    public function __construct(array $pins)
    {
        $this->pins = $pins;
    }

    /**
     * @return int
     */
    public function encode(): int
    {
        $result = 0;
        foreach ($this->pins as $pin) {
            $result += pow(2, $pin);
        }

        return $result;
    }
}