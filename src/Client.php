<?php
namespace Volantus\Pigpio;

use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\IncompleteDataException;
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

        $responseData = $this->receiveData(Response::BASE_SIZE);
        try {
            return $request->getResponseStructure()->decode($responseData);
        } catch (IncompleteDataException $e) {
            $responseData = $this->receiveData($e->getExpectedSize(), $responseData);
            return $request->getResponseStructure()->decode($responseData);
        }
    }

    /**
     * @param int    $size          In bytes
     * @param string $previousData  New data will be appended
     *
     * @return string
     */
    private function receiveData(int $size, string $previousData = ''): string
    {
        $data = $previousData . $this->socket->listen();
        if (strlen($data) != $size) {
            usleep(2000);
            return $this->receiveData($size, $data);
        }

        return $data;
    }
}