<?php
namespace Volantus\Pigpio\Tests\Notification\Event;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Notification\Event\AliveEvent;
use Volantus\Pigpio\Notification\Event\EventFactory;
use Volantus\Pigpio\Notification\Event\GpioStatus;
use Volantus\Pigpio\Notification\Event\CustomEvent;
use Volantus\Pigpio\Notification\Event\StateChangedEvent;
use Volantus\Pigpio\Notification\Event\WatchdogEvent;

/**
 * Class EventFactoryTest
 *
 * @package Volantus\Pigpio\Tests\Notification\Event
 */
class EventFactoryTest extends TestCase
{
    /**
     * @var EventFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new EventFactory();
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\Event\DecodingFailedException
     * @expectedExceptionMessage Unable to unpack data. (Message: 616263)
     */
    public function test_decode_invalidData()
    {
        $this->factory->decode('abc');
    }

    public function test_decode_aliveEventDetected()
    {
        $message = pack('SSLL', 0, 64, 0, 0);
        $result = $this->factory->decode($message);

        self::assertInstanceOf(AliveEvent::class, $result);
        self::assertTrue($result->isAliveEvent());
        self::assertFalse($result->isTimeoutEvent());
        self::assertFalse($result->istCustomEvent());
        self::assertFalse($result->isStateChangedEvent());
    }

    public function test_decode_watchdogEventDetected()
    {
        $message = pack('SSLL', 0, 32, 0, 0);
        $result = $this->factory->decode($message);

        self::assertInstanceOf(WatchdogEvent::class, $result);
        self::assertTrue($result->isTimeoutEvent());
        self::assertFalse($result->istCustomEvent());
        self::assertFalse($result->isAliveEvent());
        self::assertFalse($result->isStateChangedEvent());
    }

    public function test_decode_customEventDetected()
    {
        $message = pack('SSLL', 0, 128, 0, 0);
        $result = $this->factory->decode($message);

        self::assertInstanceOf(CustomEvent::class, $result);
        self::assertTrue($result->istCustomEvent());
        self::assertFalse($result->isTimeoutEvent());
        self::assertFalse($result->isAliveEvent());
        self::assertFalse($result->isStateChangedEvent());
    }

    public function test_decode_stateChangedEventDetected()
    {
        $message = pack('SSLL', 0, 0, 0, 0);
        $result = $this->factory->decode($message);

        self::assertInstanceOf(StateChangedEvent::class, $result);
        self::assertTrue($result->isStateChangedEvent());
        self::assertFalse($result->isTimeoutEvent());
        self::assertFalse($result->isAliveEvent());
        self::assertFalse($result->istCustomEvent());
    }

    public function test_decode_sequenceIdCorrect()
    {
        $message = pack('SSLL', 97, 0, 0, 0);
        $result = $this->factory->decode($message);

        self::assertEquals(97, $result->getSequenceId());
    }

    public function test_decode_ticksCorrect()
    {
        $message = pack('SSLL', 0, 0, 17373, 0);
        $result = $this->factory->decode($message);

        self::assertEquals(17373, $result->getTicks());
    }

    public function test_decode_gpioStatusCorrect()
    {
        $message = pack('SSLL', 0, 0, 0, 2162688);
        $result = $this->factory->decode($message);

        for ($i = 0; $i < 31; $i++) {
            $status = $result->getGpioStatus()[$i];
            self::assertInstanceOf(GpioStatus::class, $status);

            if ($status->getPin() == 16 || $status->getPin() == 21) {
                self::assertTrue($status->isHigh(), 'Failed asserting that pin ' . $status->getPin() . ' is high');
            } else {
                self::assertFalse($status->isHigh(), 'Failed asserting that pin ' . $status->getPin() . ' is low');
            }
        }

        self::assertNotNull($result->getStatus(16));
        self::assertEquals(16, $result->getStatus(16)->getPin());
        self::assertNotNull($result->getStatus(21));
        self::assertEquals(21, $result->getStatus(21)->getPin());
    }

    /**
     * @depends test_decode_watchdogEventDetected
     */
    public function test_decode_timeoutPinCorrect()
    {
        $message = pack('SSLL', 0, 41, 0, 0);
        /** @var WatchdogEvent $result */
        $result = $this->factory->decode($message);

        self::assertEquals(9, $result->getTimeoutPin());
    }

    /**
     * @depends test_decode_customEventDetected
     */
    public function test_decode_eventIdCorrect()
    {
        $message = pack('SSLL', 0, 132, 0, 0);
        /** @var CustomEvent $result */
        $result = $this->factory->decode($message);

        self::assertEquals(4, $result->getEventId());
    }
}