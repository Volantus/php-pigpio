<?php
namespace Volantus\Pigpio;

use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Protocol\IncompleteDataException;
use Volantus\Pigpio\Protocol\Request;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\Protocol\TimeoutException;

/**
 * Class Client
 *
 * @package Volantus\Pigpio
 */
class Client
{
    const SLEEP_TIME = 2000;

    /**
     * @var Socket
     */
    private $socket;

    /**
     * Time last requested started at (in seconds)
     *
     * @var float
     */
    private $requestStarted;

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
        $this->requestStarted = microtime(true);

        $responseData = $this->receiveData($request->getTimeout(), Response::BASE_SIZE);
        try {
            return $request->getResponseStructure()->decode($responseData);
        } catch (IncompleteDataException $e) {
            $timeout = $this->calcTimeout($request->getTimeout());
            $responseData = $this->receiveData($timeout, $e->getExpectedSize(), $responseData);
            return $request->getResponseStructure()->decode($responseData);
        }
    }

    /**
     * @param int    $timeout      In microseconds
     * @param int    $size         In bytes
     * @param string $previousData New data will be appended
     *
     * @return string
     */
    private function receiveData(int $timeout, int $size, string $previousData = ''): string
    {
        $data = $this->socket->listen($this->calcTimeout($timeout));
        if ($data == '') {
            // If empty data is retrieved, timeout on socket level occurred
            throw new TimeoutException("Daemon did not respond within specified timeout ($timeout µs)");
        }
        $data = $previousData . $data;

        if (strlen($data) != $size) {
            usleep(self::SLEEP_TIME);
            return $this->receiveData($timeout, $size, $data);
        }

        return $data;
    }

    /**
     * @param int $timeout
     *
     * @return int
     */
    private function calcTimeout(int $timeout): int
    {
        $delta = microtime(true) - $this->requestStarted;
        $delta *= 1000000;
        $result = $timeout -  $delta;

        if ($result <= 0) {
            throw new TimeoutException("Daemon did not respond within specified timeout ($timeout µs)");
        }

        return $result;
    }
}