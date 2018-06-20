<?php
namespace Volantus\Pigpio\Tests\HallSensor\Rev;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\HallSensor\Rev\RevolutionCounter;
use Volantus\Pigpio\HallSensor\Rev\RotationSpeed;
use Volantus\Pigpio\Notification\Event\AliveEvent;
use Volantus\Pigpio\Notification\Event\GpioStatus;
use Volantus\Pigpio\Notification\Event\StateChangedEvent;
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\Protocol\Bitmap;

/**
 * Class RevolutionCounterTest
 *
 * @package Volantus\Pigpio\Tests\HallSensor\Rev
 */
class RevolutionCounterTest extends TestCase
{
    /**
     * @var Notifier|MockObject
     */
    private $notifier;

    /**
     * @var RevolutionCounter
     */
    private $counter;

    protected function setUp()
    {
        $this->notifier = $this->getMockBuilder(Notifier::class)->disableOriginalConstructor()->getMock();
        $this->counter = new RevolutionCounter($this->notifier);
    }

    public function test_start_notifierOpened_true()
    {
        $this->notifier->method('isOpen')->willReturn(false);
        $this->notifier->expects(self::once())
            ->method('open');

        $this->counter->start(22, function (RotationSpeed $speed) {});
    }

    public function test_start_notifierOpened_false()
    {
        $this->notifier->method('isOpen')->willReturn(true);
        $this->notifier->expects(self::never())
            ->method('open');

        $this->counter->start(22, function (RotationSpeed $speed) {});
    }

    public function test_start_firstHigh_noTrigger()
    {
        $this->notifier->method('isOpen')->willReturn(true);
        $this->notifier->expects(self::never())
            ->method('open');

        $notifierCallback = null;
        $this->notifier->expects(self::once())->method('start')
            ->with(self::equalTo(new Bitmap([22])))
            ->will(self::returnCallback(function (Bitmap $gpioPins, callable $callback) use (&$notifierCallback) {
                $notifierCallback = $callback;
            }));

        $this->notifier->method('tick')
            ->will(self::returnCallback(function () use (&$notifierCallback) {
                call_user_func($notifierCallback, new StateChangedEvent(1, 100, [21 => new GpioStatus(21, false)]));
                call_user_func($notifierCallback, new StateChangedEvent(2, 200, [21 => new GpioStatus(21, true)]));
            }));

        $this->counter->start(22, function (RotationSpeed $speed) {
            throw new \RuntimeException('Did not expect trigger on first high signal');
        });
        $this->counter->tick();
    }

    public function test_start_otherEventType_noTrigger()
    {
        $this->notifier->method('isOpen')->willReturn(true);
        $this->notifier->expects(self::never())
            ->method('open');

        $notifierCallback = null;
        $this->notifier->expects(self::once())->method('start')
            ->with(self::equalTo(new Bitmap([22])))
            ->will(self::returnCallback(function (Bitmap $gpioPins, callable $callback) use (&$notifierCallback) {
                $notifierCallback = $callback;
            }));

        $this->notifier->method('tick')
            ->will(self::returnCallback(function () use (&$notifierCallback) {
                call_user_func($notifierCallback, new AliveEvent(1, 100, [21 => new GpioStatus(21, true)]));
                call_user_func($notifierCallback, new AliveEvent(2, 200, [21 => new GpioStatus(21, true)]));
            }));

        $this->counter->start(22, function (RotationSpeed $speed) {
            throw new \RuntimeException('RevolutionCounter handled wrong event type');
        });
        $this->counter->tick();
    }

    public function test_start_otherEventType_correctDelta()
    {
        $this->notifier->method('isOpen')->willReturn(true);
        $this->notifier->expects(self::never())
            ->method('open');

        $notifierCallback = null;
        $this->notifier->expects(self::once())->method('start')
            ->with(self::equalTo(new Bitmap([22])))
            ->will(self::returnCallback(function (Bitmap $gpioPins, callable $callback) use (&$notifierCallback) {
                $notifierCallback = $callback;
            }));

        $this->notifier->method('tick')
            ->will(self::returnCallback(function () use (&$notifierCallback) {
                call_user_func($notifierCallback, new StateChangedEvent(1, 80, [21 => new GpioStatus(21, false)]));
                call_user_func($notifierCallback, new StateChangedEvent(1, 100, [21 => new GpioStatus(21, true)]));
                call_user_func($notifierCallback, new StateChangedEvent(1, 1580, [21 => new GpioStatus(21, false)]));
                call_user_func($notifierCallback, new StateChangedEvent(2, 1600, [21 => new GpioStatus(21, true)]));
            }));

        /** @var RotationSpeed $result */
        $result = null;
        $this->counter->start(22, function (RotationSpeed $speed) use (&$result) {
            $result = $speed;
        });
        $this->counter->tick();

        self::assertNotNull($result);
        self::assertEquals(1500, $result->getMicroDelta());
        self::assertEquals(40000, $result->getRpm());
    }

    public function test_tick_blocking_true()
    {
        $this->notifier->expects(self::once())
            ->method('tick')
            ->with(self::equalTo(true));

        $this->counter->tick(true);
    }

    public function test_tick_blocking_false()
    {
        $this->notifier->expects(self::once())
            ->method('tick')
            ->with(self::equalTo(false));

        $this->counter->tick(false);
    }
}