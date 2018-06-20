<?php
namespace Volantus\Pigpio\HallSensor\Rev;

/**
 * Class RotationSpeed
 *
 * @package Volantus\Pigpio\HallSensor\Rev
 */
class RotationSpeed
{
    /**
     * Delta in microseconds between last two triggers
     *
     * @var int
     */
    private $microDelta;

    /**
     * Rotations per minute
     *
     * @var float
     */
    private $rpm;

    /**
     * RotationSpeed constructor.
     *
     * @param int $microDelta
     */
    public function __construct(int $microDelta)
    {
        $this->microDelta = $microDelta;
        $this->rpm = 60000000 / $this->microDelta;
    }

    /**
     * Returns delta in microseconds between last two triggers
     *
     * @return int
     */
    public function getMicroDelta(): int
    {
        return $this->microDelta;
    }

    /**
     * Return rotations per minute
     *
     * @return float
     */
    public function getRpm(): float
    {
        return $this->rpm;
    }
}