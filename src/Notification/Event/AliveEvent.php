<?php
namespace Volantus\Pigpio\Notification\Event;

/**
 * Class AliveSignalEvent
 *
 * @package Volantus\Pigpio\Notification\Event
 */
class AliveEvent extends GpioEvent
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
        return true;
    }

    /**
     * @return bool
     */
    public function isStateChangedEvent(): bool
    {
        return false;
    }
}