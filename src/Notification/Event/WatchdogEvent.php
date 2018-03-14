<?php
namespace Volantus\Pigpio\Notification\Event;

/**
 * Class WatchdogEvent
 *
 * @package Volantus\Pigpio\Notification\Event
 */
class WatchdogEvent extends GpioEvent
{
    /**
     * Pin which had an timeout
     *
     * @var int
     */
    private $timeoutPin;

    /**
     * @param int   $sequence
     * @param int   $ticks
     * @param array $gpioStatus
     * @param int   $timeoutPin
     */
    public function __construct(int $sequence, int $ticks, array $gpioStatus, int $timeoutPin)
    {
        parent::__construct($sequence, $ticks, $gpioStatus);
        $this->timeoutPin = $timeoutPin;
    }

    /**
     * @return bool
     */
    public function istCustomEvent(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isTimeoutEvent(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAliveEvent(): bool
    {
        return false;
    }

    /**
     * @return int
     */
    public function getTimeoutPin(): int
    {
        return $this->timeoutPin;
    }

    /**
     * @return bool
     */
    public function isStateChangedEvent(): bool
    {
        return false;
    }
}