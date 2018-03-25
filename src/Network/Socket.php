<?php
namespace Volantus\Pigpio\Network;

/**
 * Class Socket
 *
 * @package Volantus\Pigpio\Network
 */
class Socket
{
    /**
     * @var resource
     */
    private $connection;

    /**
     * @param string $address
     * @param int    $port
     */
    public function __construct(string $address, int $port)
    {
        $this->connection = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->connection === false) {
            throw new SocketException('socket_create() failed: ' . socket_strerror(socket_last_error()));
        }

        $result = @socket_connect($this->connection, $address, $port);
        if ($result === false) {
            throw new SocketException("socket_connect() failed ($result):" . socket_strerror(socket_last_error($this->connection)));
        }
    }

    /**
     * @param string $data
     */
    public function send(string $data)
    {
        socket_write($this->connection, $data);
    }

    /**
     * @param int $timeout
     *
     * @return string
     * @internal param bool $blocking
     *
     */
    public function listen(int $timeout = 0) : string
    {
        socket_set_timeout($this->connection, 0, $timeout);

        $fullData = socket_read($this->connection, 64);
        // Checking for more data
        socket_set_nonblock($this->connection);
        while ($moreData = socket_read($this->connection, 64)) {
            $fullData .= $moreData;
        }
        socket_set_block($this->connection);
        return $fullData;
    }

    public function __destruct()
    {
        socket_close($this->connection);
    }
}