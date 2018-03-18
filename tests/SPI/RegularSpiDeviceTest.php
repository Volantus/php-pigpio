<?php
namespace Volantus\Pigpio\Tests\SPI;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\ExtensionRequest;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\SPI\RegularSpiDevice;

/**
 * Class RegularSpiDeviceTest
 *
 * @package Volantus\Pigpio\Tests\SPI
 */
class RegularSpiDeviceTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var RegularSpiDevice
     */
    private $device;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->device = new RegularSpiDevice($this->client);
    }

    public function test_open_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::SPIO, 1, 32000, 'L', [32])))
            ->willReturn(new Response(4));

        $this->device->open(1, 32000, 32);
    }

    public function test_open_calledTwice_idempotent()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new ExtensionRequest(Commands::SPIO, 1, 32000, 'L', [32])))
            ->willReturn(new Response(4));

        $this->device->open(1, 32000, 32);
        $this->device->open(0, 32000, 32);
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => bad SPI channel given (PI_BAD_SPI_CHANNEL)
     */
    public function test_open_failed_badChannel()
    {
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_SPI_CHANNEL);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_SPI_CHANNEL));

        $this->device->open(8, 32000, 0);
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => bad speed given (PI_BAD_SPI_SPEED)
     */
    public function test_open_failed_badSpeed()
    {
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_SPI_SPEED);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_SPI_SPEED));

        $this->device->open(0, -1, 0);
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => bad flags given (PI_BAD_FLAGS)
     */
    public function test_open_failed_badFlags()
    {
        $this->expectExceptionCode(RegularSpiDevice::PI_BAD_FLAGS);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_BAD_FLAGS));

        $this->device->open(0, 32000, -100);
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => no AUX available (PI_NO_AUX_SPI)
     */
    public function test_open_failed_noAux()
    {
        $this->expectExceptionCode(RegularSpiDevice::PI_NO_AUX_SPI);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_NO_AUX_SPI));

        $this->device->open(0, 32000, -100);
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed (PI_SPI_OPEN_FAILED)
     */
    public function test_open_failed_openingFailed()
    {
        $this->expectExceptionCode(RegularSpiDevice::PI_SPI_OPEN_FAILED);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(RegularSpiDevice::PI_SPI_OPEN_FAILED));

        $this->device->open(0, 32000, 0);
    }

    /**
     * @expectedException \Volantus\Pigpio\SPI\OpeningDeviceFailedException
     * @expectedExceptionMessage Opening device failed => unknown error
     */
    public function test_open_failed_unknownError()
    {
        $this->expectExceptionCode(-512);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->willReturn(new Response(-512));

        $this->device->open(0, 32000, 0);
    }
}