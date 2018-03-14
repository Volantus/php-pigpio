<?php
namespace Volantus\Pigpio\Notification\Event;

/**
 * Class StateChangedEvent
 *
 * @package Volantus\Pigpio\Notification\Event
 */
class StateChangedEvent extends GpioEvent
{
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
        return false;
    }

    /**
     * @return bool
     */
    public function isAliveEvent(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isStateChangedEvent(): bool
    {
        return true;
    }
}