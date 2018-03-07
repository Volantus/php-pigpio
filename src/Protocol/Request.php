<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Interface Request
 *
 * @package Volantus\Pigpio\Protocol
 */
interface Request
{
    /**
     * @return string
     */
    public function encode(): string;

    /**
     * @return ResponseStructure
     */
    public function getResponseStructure(): ResponseStructure;
}