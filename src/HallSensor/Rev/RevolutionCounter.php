<?php
namespace Volantus\Pigpio\HallSensor\Rev;

use Volantus\Pigpio\Notification\Event\GpioEvent;
use Volantus\Pigpio\Notification\Event\StateChangedEvent;
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\Protocol\Bitmap;

/**
 * Class RevolutionCounter
 *
 * @package Volantus\Pigpio\HallSensor\Rev
 */
class RevolutionCounter
{
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var int
     */
    private $lastHigh;

    /**
     * @var callable
     */
    private $callback;

    /**
     * RevolutionCounter constructor.
     *
     * @param Notifier $notifier
     */
    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * @param int      $gpioPin
     * @param callable $callback
     */
    public function start(int $gpioPin, callable $callback)
    {
        $this->lastHigh = null;
        $this->callback = $callback;

        if (!$this->notifier->isOpen()) {
            $this->notifier->open();
        }

        $this->notifier->start(new Bitmap([$gpioPin]), function (GpioEvent $event) use ($gpioPin) {
            if ($event instanceof StateChangedEvent) {
                $this->handleEvent($event, $gpioPin);
            }
        });
    }

    /**
     * Checks for new events and calls callback on new data
     *
     * @param bool $blocking If true, function blocks until new data is received
     */
    public function tick($blocking = false)
    {
        $this->notifier->tick($blocking);
    }

    /**
     * @param StateChangedEvent $event
     * @param int               $gpioPin
     */
    private function handleEvent(StateChangedEvent $event, int $gpioPin)
    {
        if (!$event->getStatus($gpioPin)->isHigh()) {
            return;
        }

        if ($this->lastHigh !== null) {
            $delta = $event->getTicks() - $this->lastHigh;
            call_user_func($this->callback, new RotationSpeed($delta));
        }

        $this->lastHigh = $event->getTicks();
    }
}