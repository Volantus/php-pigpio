<?php
namespace Volantus\Pigpio\Tests\Network;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Network\Socket;

/**
 * Class SocketTest
 *
 * @package Volantus\Pigpio\Tests\Network
 */
class SocketTest extends TestCase
{
    /**
     * @expectedException \Volantus\Pigpio\Network\SocketException
     * @expectedExceptionMessage socket_connect() failed
     */
    public function test_construct_connectFailed()
    {
        new Socket('256.0.0.1', 80);
    }
}