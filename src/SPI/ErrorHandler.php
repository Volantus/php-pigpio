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
    public function handleTransfer(Request $request, Response $response);

    /**
     * @param Response $response
     *
     * @return mixed
     */
    public function handleOpen(Response $response);

    /**
     * @param Response $response
     *
     * @return mixed
     */
    public function handleClose(Response $response);
}