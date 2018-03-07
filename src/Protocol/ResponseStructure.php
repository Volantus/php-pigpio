<?php
namespace Volantus\Pigpio\Protocol;

/**
 * Interface ResponseStructure
 *
 * @package Volantus\Pigpio\Protocol
 */
interface ResponseStructure
{
    /**
     * @param string $data
     *
     * @return Response
     */
    public function decode(string $data): Response;
}