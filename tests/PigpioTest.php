<?php
namespace Volantus\Pigpio\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Pigpio;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\Protocol\ResponseStructure;

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

    public function test_sendRaw_correct()
    {
        $expectedResponse = new Response(0, null);

        $responseStructure = $this->getMockBuilder(ResponseStructure::class)->getMock();
        $responseStructure->expects(self::once())
            ->method('decode')
            ->with(self::equalTo('correct response data'))
            ->willReturn($expectedResponse);

        $request = new DefaultRequest(8, 21, 1500, $responseStructure);

        $this->socket->expects(self::once())
            ->method('send')
            ->with(self::equalTo($request->encode()));

        $this->socket->expects(self::once())
            ->method('listen')
            ->willReturn('correct response data');

        $result = $this->service->sendRaw($request);

        self::assertSame($result, $expectedResponse);
    }
}