<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\Request;
use Volantus\Pigpio\Protocol\Response;

/**
 * Class BitBangingDeviceErrorHandler
 *
 * @package Volantus\Pigpio\SPI
 */
class BitBangingDeviceErrorHandler implements ErrorHandler
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    public function handleTransfer(Request $request, Response $response)
    {
        // TODO: Implement handleTransfer() method.
    }

    /**
     * @param Response $response
     *
     * @return mixed
     */
    public function handleOpen(Response $response)
    {
        switch ($response->getResponse()) {
            case BitBaningSpiDevice::PI_BAD_USER_GPIO:
                throw new OpeningDeviceFailedException('Opening device failed => bad GPIO pin given (PI_BAD_USER_GPIO)', BitBaningSpiDevice::PI_BAD_USER_GPIO);
                break;
            case BitBaningSpiDevice::PI_BAD_SPI_BAUD:
                throw new OpeningDeviceFailedException('Opening device failed => bad baud rate given (PI_BAD_SPI_BAUD)', BitBaningSpiDevice::PI_BAD_SPI_BAUD);
                break;
            case BitBaningSpiDevice::PI_GPIO_IN_USE:
                throw new OpeningDeviceFailedException('Opening device failed => GPIO pin is already in use (PI_GPIO_IN_USE)', BitBaningSpiDevice::PI_GPIO_IN_USE);
                break;
            default:
                throw new OpeningDeviceFailedException('Opening device failed => unknown error', $response->getResponse());
                break;
        }
    }

    /**
     * @param Response $response
     *
     * @return mixed
     */
    public function handleClose(Response $response)
    {
        // TODO: Implement handleClose() method.
    }
}