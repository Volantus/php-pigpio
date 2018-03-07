<?php
namespace Volantus\Pigpio;

use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\Request;
use Volantus\Pigpio\Protocol\Response;

/**
 * Class Client
 *
 * @package Volantus\Pigpio
 */
class Client
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
     * @param Request $request
     *
     * @return Response
     */
    public function sendRaw(Request $request): Response
    {
        $this->socket->send($request->encode());

        $responseData = $this->socket->listen();
        return $request->getResponseStructure()->decode($responseData);
    }
}