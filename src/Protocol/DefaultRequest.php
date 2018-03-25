<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Class DefaultRequest
 *
 * @package Volantus\Pigpio\Protocol
 */
class DefaultRequest implements Request
{
    /**
     * Unsigned integer (32 bit)
     * Currently 0 - 177 supported
     *
     * @var int
     */
    private $command;

    /**
     * Unsigned integer (32 bit)
     *
     * @var int
     */
    private $p1;

    /**
     * Unsigned integer (32 bit)
     *
     * @var int
     */
    private $p2;

    /**
     * @var ResponseStructure
     */
    private $responseStructure;

    /**
     * @var int
     */
    private $timeout;

    /**
     * NormalRequest constructor.
     *
     * @param int               $command           Command (0 - 177)
     * @param int               $p1                First parameter - Unsigned integer (32 bit)
     * @param int               $p2                Second parameter - Unsigned integer (32 bit)
     * @param ResponseStructure $responseStructure Structure of the expected response
     * @param int               $timeout           Timeout in microseconds for the response
     */
    public function __construct(int $command, int $p1, int $p2, ResponseStructure $responseStructure = null, int $timeout = 100000)
    {
        $this->command = $command;
        $this->p1 = $p1;
        $this->p2 = $p2;
        $this->responseStructure = $responseStructure ?: new DefaultResponseStructure();
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function encode(): string
    {
        return pack('LLLL', $this->command, $this->p1, $this->p2, 0);
    }

    /**
     * @return ResponseStructure
     */
    public function getResponseStructure(): ResponseStructure
    {
        return $this->responseStructure;
    }

    /**
     * @return int
     */
    public function getCommand(): int
    {
        return $this->command;
    }

    /**
     * @return int
     */
    public function getP1(): int
    {
        return $this->p1;
    }

    /**
     * @return int
     */
    public function getP2(): int
    {
        return $this->p2;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}