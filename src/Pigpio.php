<?php
namespace Volantus\Pigpio;

use Volantus\Pigpio\Network\Socket;

/**
 * Class Pigpio
 *
 * @package Volantus\Pigpio
 */
class Pigpio
{
    /**
     * @var Socket
     */
    private $socket;

    /**
     * @param Socket $socket
     */
    public function __construct(Socket $socket = null)
    {
        $this->socket = $socket ?: new Socket('127.0.0.1', 8888);
    }

    /**
     * Sends raw commands to the pigpio daemon
     *
     * @param int  $command Command (0 - 117)
     * @param int  $p1      First parameter (positive 32 bit)
     * @param int  $p2      Second parameter (positive 32 bit)
     * @param int  $p3      Third parameter (positive 32 bit)
     */
    public function sendRaw(int $command, int $p1, int $p2 = 0, int $p3 = 0)
    {
        $message = pack('L*', $command, $p1, $p2, $p3);
        $this->socket->send($message);
    }

    /**
     * Reads data from the pigpiod socket
     * This function is blocking until a response is retrieved
     *
     * @param bool $signed True if the response should be treated as signed integer, false on unsigned integer
     *
     * @return array
     */
    public function readRaw(bool $signed = true): array
    {
        $response = $this->socket->listen();
        $format = $signed ? 'l*' : 'L*';

        return unpack($format, $response);
    }
}