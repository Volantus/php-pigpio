<?php
namespace Volantus\Pigpio\Protocol;

use Throwable;

/**
 * Class IncompleteDataException
 *
 * @package Volantus\Pigpio\Protocol
 */
class IncompleteDataException extends \Exception
{
    /**
     * @var int
     */
    private $expectedSize;

    /**
     * @param string         $message
     * @param int            $expectedSize
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $expectedSize, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->expectedSize = $expectedSize;
    }

    /**
     * @return int
     */
    public function getExpectedSize(): int
    {
        return $this->expectedSize;
    }
}