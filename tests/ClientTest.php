<?php
namespace Volantus\Pigpio\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\IncompleteDataException;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\Protocol\ResponseStructure;

/**
 * Class ClientTest
 *
 * @package Volantus\Pigpio\Tests
 */
class ClientTest extends TestCase
{
    /**
     * @var Socket|MockObject
     */
    private $socket;

    /**
     * @var Client
     */
    private $service;

    protected function setUp()
    {
        $this->socket = $this->getMockBuilder(Socket::class)->disableOriginalConstructor()->getMock();
        $this->service = new Client($this->socket);
    }

    public function test_sendRaw_correct()
    {
        $expectedResponse = new Response(0, null);

        $responseStructure = $this->getMockBuilder(ResponseStructure::class)->getMock();
        $responseStructure->expects(self::once())
            ->method('decode')
            ->with(self::equalTo('ABCDEFGHIJKLMNOP'))
            ->willReturn($expectedResponse);

        $request = new DefaultRequest(8, 21, 1500, $responseStructure);

        $this->socket->expects(self::once())
            ->method('send')
            ->with(self::equalTo($request->encode()));

        $this->socket->expects(self::once())
            ->method('listen')
            ->willReturn('ABCDEFGHIJKLMNOP');

        $result = $this->service->sendRaw($request);

        self::assertSame($result, $expectedResponse);
    }

    public function test_sendRaw_headerNotComplete()
    {
        $expectedResponse = new Response(0, null);

        $responseStructure = $this->getMockBuilder(ResponseStructure::class)->getMock();
        $responseStructure->expects(self::once())
            ->method('decode')
            ->with(self::equalTo('ABCDEFGHIJKLMNOP'))
            ->willReturn($expectedResponse);

        $request = new DefaultRequest(8, 21, 1500, $responseStructure);

        $this->socket->expects(self::at(0))
            ->method('send')
            ->with(self::equalTo($request->encode()));

        $this->socket->expects(self::at(1))
            ->method('listen')
            ->willReturn('ABCDEFGHIJKLMN');

        $this->socket->expects(self::at(2))
            ->method('listen')
            ->willReturn('OP');

        $result = $this->service->sendRaw($request);

        self::assertSame($result, $expectedResponse);
    }

    public function test_sendRaw_incompleteDataException()
    {
        $expectedResponse = new Response(0, null);

        $responseStructure = $this->getMockBuilder(ResponseStructure::class)->getMock();
        $responseStructure->expects(self::at(0))
            ->method('decode')
            ->with(self::equalTo('ABCDEFGHIJKLMNOP'))
            ->willThrowException(new IncompleteDataException('test', 20));

        $responseStructure->expects(self::at(1))
            ->method('decode')
            ->with(self::equalTo('ABCDEFGHIJKLMNOPQRST'))
            ->willReturn($expectedResponse);

        $request = new DefaultRequest(8, 21, 1500, $responseStructure);

        $this->socket->expects(self::at(0))
            ->method('send')
            ->with(self::equalTo($request->encode()));

        $this->socket->expects(self::at(1))
            ->method('listen')
            ->willReturn('ABCDEFGHIJKLMN');

        $this->socket->expects(self::at(2))
            ->method('listen')
            ->willReturn('OP');

        $this->socket->expects(self::at(3))
            ->method('listen')
            ->willReturn('QR');

        $this->socket->expects(self::at(4))
            ->method('listen')
            ->willReturn('ST');

        $result = $this->service->sendRaw($request);

        self::assertSame($result, $expectedResponse);
    }
}