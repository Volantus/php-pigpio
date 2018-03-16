<?php
namespace Volantus\Pigpio\PWM;

use Volantus\Pigpio\Notification\Event\GpioEvent;

/**
 * Class PwmSignal
 *
 * @package Volantus\Pigpio\PWM
 */
class PwmSignal
{
    /**
     * @var int
     */
    private $gpioPin;

    /**
     * Pulse width in microseconds, usually between 1000 and 2000 microseconds
     *
     * @var int
     */
    private $pulseWidth;

    /**
     * PwmSignal constructor.
     *
     * @param int         $gpioPin
     * @param int         $pulseWidth
     */
    public function __construct(int $gpioPin, int $pulseWidth)
    {
        $this->gpioPin = $gpioPin;
        $this->pulseWidth = $pulseWidth;
    }

    /**
     * @return int
     */
    public function getGpioPin(): int
    {
        return $this->gpioPin;
    }

    /**
     * @return int
     */
    public function getPulseWidth(): int
    {
        return $this->pulseWidth;
    }
}