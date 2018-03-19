<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Protocol\Request;
use Volantus\Pigpio\Protocol\Response;

/**
 * Interface ErrorHandler
 *
 * @package Volantus\Pigpio\SPI
 */
interface ErrorHandler
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    public function handle(Request $request, Response $response);
}