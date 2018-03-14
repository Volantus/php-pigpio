<?php
namespace Volantus\Pigpio\Notification\Event;

/**
 * Class GpioStatus
 *
 * @package Volantus\Pigpio\Notification\Event
 */
class GpioStatus
{
    /**
     * @var int
     */
    private $pin;

    /**
     * @var bool
     */
    private $high;

    /**
     * GpioStatus constructor.
     *
     * @param int  $pin
     * @param bool $high
     */
    public function __construct(int $pin, bool $high)
    {
        $this->pin = $pin;
        $this->high = $high;
    }

    /**
     * @return int
     */
    public function getPin(): int
    {
        return $this->pin;
    }

    /**
     * @return bool
     */
    public function isHigh(): bool
    {
        return $this->high;
    }
}