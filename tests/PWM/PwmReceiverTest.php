<?php
namespace Volantus\Pigpio\Tests\PWM;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Notification\Event\AliveEvent;
use Volantus\Pigpio\Notification\Event\GpioStatus;
use Volantus\Pigpio\Notification\Event\StateChangedEvent;
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\Protocol\Bitmap;
use Volantus\Pigpio\PWM\PwmReceiver;
use Volantus\Pigpio\PWM\PwmSignal;

/**
 * Class PwmReceiverTest
 *
 * @package Volantus\Pigpio\Tests\PWM
 */
class PwmReceiverTest extends TestCase
{
    /**
     * @var Notifier|MockObject
     */
    private $notifier;

    /**
     * @var PwmReceiver
     */
    private $receiver;

    protected function setUp()
    {
        $this->notifier = $this->getMockBuilder(Notifier::class)->disableOriginalConstructor()->getMock();
        $this->receiver = new PwmReceiver($this->notifier);
    }

    public function test_start_wrongEventType()
    {
        $this->notifier->expects(self::once())
            ->method('start')
            ->with(self::equalTo(new Bitmap([16])))
            ->will(self::returnCallback(function (Bitmap $pins, callable $callback){
                call_user_func($callback, new AliveEvent(0, 1000, [15 => new GpioStatus(16, true)]));
                call_user_func($callback, new AliveEvent(1, 2000, [15 => new GpioStatus(16, false)]));
            }));

        $result = null;
        $this->receiver->start([16], function (PwmSignal $signal) use (&$result) {
            $result = $signal;
        });

        self::assertNull($result);
    }

    public function test_start_firstSignalIsLow()
    {
        $this->notifier->expects(self::once())
            ->method('start')
            ->with(self::equalTo(new Bitmap([16])))
            ->will(self::returnCallback(function (Bitmap $pins, callable $callback){
                call_user_func($callback, new StateChangedEvent(0, 1000, [15 => new GpioStatus(16, false)]));
                call_user_func($callback, new StateChangedEvent(1, 2000, [15 => new GpioStatus(16, true)]));
            }));

        $result = null;
        $this->receiver->start([16], function (PwmSignal $signal) use (&$result) {
            $result = $signal;
        });

        self::assertNull($result);
    }

    public function test_start_correctPulseWidth_normalCase()
    {
        $this->notifier->expects(self::once())
            ->method('start')
            ->with(self::equalTo(new Bitmap([16])))
            ->will(self::returnCallback(function (Bitmap $pins, callable $callback){
                call_user_func($callback, new StateChangedEvent(0, 1000, [15 => new GpioStatus(16, true)]));
                call_user_func($callback, new StateChangedEvent(1, 2200, [15 => new GpioStatus(16, false)]));
            }));

        $result = null;
        $this->receiver->start([16], function (PwmSignal $signal) use (&$result) {
            $result = $signal;
            self::assertEquals(16, $signal->getGpioPin());
            self::assertEquals(1200, $signal->getPulseWidth());
        });

        self::assertNotNull($result);
    }

    public function test_start_correctPulseWidth_32Bit_bothTicksNegative()
    {
        $this->notifier->expects(self::once())
            ->method('start')
            ->with(self::equalTo(new Bitmap([16])))
            ->will(self::returnCallback(function (Bitmap $pins, callable $callback){
                call_user_func($callback, new StateChangedEvent(0, -1800, [15 => new GpioStatus(16, true)]));
                call_user_func($callback, new StateChangedEvent(1, -500, [15 => new GpioStatus(16, false)]));
            }));

        $result = null;
        $this->receiver->start([16], function (PwmSignal $signal) use (&$result) {
            $result = $signal;
            self::assertEquals(16, $signal->getGpioPin());
            self::assertEquals(1300, $signal->getPulseWidth());
        });

        self::assertNotNull($result);
    }

    public function test_start_correctPulseWidth_32Bit_timerWrappedAround()
    {
        $this->notifier->expects(self::once())
            ->method('start')
            ->with(self::equalTo(new Bitmap([16])))
            ->will(self::returnCallback(function (Bitmap $pins, callable $callback){
                call_user_func($callback, new StateChangedEvent(0, PwmReceiver::MAX_32_BIT_INT - 50, [15 => new GpioStatus(16, true)]));
                call_user_func($callback, new StateChangedEvent(1, 0 - PwmReceiver::MAX_32_BIT_INT + 1800, [15 => new GpioStatus(16, false)]));
            }));

        $result = null;
        $this->receiver->start([16], function (PwmSignal $signal) use (&$result) {
            $result = $signal;
            self::assertEquals(16, $signal->getGpioPin());
            self::assertEquals(1850, $signal->getPulseWidth());
        });

        self::assertNotNull($result);
    }

    public function test_start_correctPulseWidth_multiplePins_overlappingPulse()
    {
        $this->notifier->expects(self::once())
            ->method('start')
            ->with(self::equalTo(new Bitmap([16, 21])))
            ->will(self::returnCallback(function (Bitmap $pins, callable $callback){
                call_user_func($callback, new StateChangedEvent(0, 1000, [
                    15 => new GpioStatus(16, true),
                    20 => new GpioStatus(21, false)
                ]));
                call_user_func($callback, new StateChangedEvent(1, 1100, [
                    15 => new GpioStatus(16, true),
                    20 => new GpioStatus(21, true)
                ]));
                call_user_func($callback, new StateChangedEvent(1, 2100, [
                    15 => new GpioStatus(16, false),
                    20 => new GpioStatus(21, true)
                ]));
                call_user_func($callback, new StateChangedEvent(1, 2600, [
                    15 => new GpioStatus(16, false),
                    20 => new GpioStatus(21, false)
                ]));
            }));

        /** @var PwmSignal[] $signals */
        $signals = [];
        $this->receiver->start([16, 21], function (PwmSignal $signal) use (&$signals) {
            $signals[] = $signal;
        });

        self::assertCount(2, $signals, 'Expected retrieving exactly two events');
        self::assertEquals(16, $signals[0]->getGpioPin());
        self::assertEquals(1100, $signals[0]->getPulseWidth());
        self::assertEquals(21, $signals[1]->getGpioPin());
        self::assertEquals(1500, $signals[1]->getPulseWidth());
    }
}