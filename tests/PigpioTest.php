<?php
namespace Volantus\Pigpio\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Pigpio;

/**
 * Class PigpioTest
 *
 * @package Volantus\Pigpio\Tests
 */
class PigpioTest extends TestCase
{
    /**
     * @var Socket|MockObject
     */
    private $socket;

    /**
     * @var Pigpio
     */
    private $service;

    protected function setUp()
    {
        $this->socket = $this->getMockBuilder(Socket::class)->disableOriginalConstructor()->getMock();
        $this->service = new Pigpio($this->socket);
    }

    public function test_sendRaw_correctEncoding()
    {
        $this->socket->expects(self::once())
            ->method('send')
            ->with(self::equalTo(hex2bin('0800000015000000dc05000000000000')));

        $this->service->sendRaw(8, 21, 1500, 0);
    }

    public function test_readRaw_signed_decodedCorrectly()
    {
        $this->socket->expects(self::once())
            ->method('listen')
            ->willReturn(pack('l', -1024));

        $result = $this->service->readRaw(true);
        self::assertEquals([1 => -1024], $result);
    }

    public function test_readRaw_unsigned_decodedCorrectly()
    {
        $this->socket->expects(self::once())
            ->method('listen')
            ->willReturn(pack('L', 1024));

        $result = $this->service->readRaw(false);
        self::assertEquals([1 => 1024], $result);
    }
}