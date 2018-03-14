<?php
namespace Volantus\Pigpio\Notification\Event;

/**
 * Class SignalEvent
 *
 * @package Volantus\Pigpio\Notification\Event
 */
class CustomEvent extends GpioEvent
{
    /**
     * Exact ID of event, which has been fired (0-31), as custom events are supported by Pigpio
     *
     * @var int
     */
    private $eventId;

    /**
     * @param int   $sequence
     * @param int   $ticks
     * @param array $gpioStatus
     * @param int   $eventId
     */
    public function __construct(int $sequence, int $ticks, array $gpioStatus, int $eventId)
    {
        parent::__construct($sequence, $ticks, $gpioStatus);
        $this->eventId = $eventId;
    }

    /**
     * @return bool
     */
    public function istCustomEvent(): bool
    {
        return true;
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
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @return bool
     */
    public function isStateChangedEvent(): bool
    {
        return false;
    }
}