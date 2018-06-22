<?php
namespace Volantus\Pigpio\Tests\PWM;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\PWM\PwmSender;

/**
 * Class PwmSenderTest
 *
 * @package Volantus\Pigpio\Tests\PWM
 */
class PwmSenderTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var PwmSender
     */
    private $sender;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->sender = new PwmSender($this->client);
    }

    public function test_setPulseWidth_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SERVO, 14, 1700)))
            ->willReturn(new Response(0));

        $this->sender->setPulseWidth(14, 1700);
    }

    /**
     * @expectedException \Volantus\Pigpio\PWM\CommandFailedException
     * @expectedExceptionMessage SERVO command failed with status code -3
     */
    public function test_setPulseWidth_failure()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::SERVO, 14, 1700)))
            ->willReturn(new Response(-3));

        $this->sender->setPulseWidth(14, 1700);
    }
}