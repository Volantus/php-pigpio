<?php
namespace Volantus\Pigpio\PWM;

use Volantus\Pigpio\Notification\Event\GpioEvent;
use Volantus\Pigpio\Notification\Event\GpioStatus;
use Volantus\Pigpio\Notification\Event\StateChangedEvent;
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\Protocol\Bitmap;

/**
 * Class PwmReceiver
 *
 * @package Volantus\Pigpio\PWM
 */
class PwmReceiver
{
    const MAX_32_BIT_INT = 2147483647;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var int[]
     */
    private $lastHigh = [];

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var array
     */
    private $gpioPins;

    /**
     * PwmReceiver constructor.
     *
     * @param Notifier $notifier
     */
    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * @param array    $pins
     * @param callable $callback
     */
    public function start(array $pins, callable $callback)
    {
        $this->callback = $callback;
        $this->gpioPins = $pins;

        $this->notifier->open();
        $this->notifier->start(new Bitmap($pins), function (GpioEvent $event) {
            if ($event->isStateChangedEvent()) {
                /** @var StateChangedEvent $event */
                $this->handleEvent($event);
            }
        });
    }

    /**
     * @param StateChangedEvent $event
     */
    private function handleEvent(StateChangedEvent $event)
    {
        foreach ($this->gpioPins as $gpioPin) {
            $status = $event->getStatus($gpioPin);
            $this->handlePinStatus($event, $status);
        }
    }

    /**
     * @param StateChangedEvent $event
     * @param GpioStatus        $status
     */
    private function handlePinStatus(StateChangedEvent $event, GpioStatus $status)
    {
        if ($status->isHigh() && !isset($this->lastHigh[$status->getPin()])) {
            $this->lastHigh[$status->getPin()] = $event->getTicks();
            return;
        }

        if (!$status->isHigh() && isset($this->lastHigh[$status->getPin()])) {
            $lastHigh = $this->lastHigh[$status->getPin()];
            $pulseWidth = GpioEvent::calculateTicksDelta($lastHigh, $event->getTicks());
            $signal = new PwmSignal($status->getPin(), $pulseWidth);

            call_user_func($this->callback, $signal);
            unset($this->lastHigh[$status->getPin()]);
        }
    }
}