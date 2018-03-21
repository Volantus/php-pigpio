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
        switch ($response->getResponse()) {
            case BitBangingSpiDevice::PI_BAD_USER_GPIO:
                throw new TransferFailedException('Cross transfer failed (internal library error) => bad GPIO pin given (PI_BAD_USER_GPIO)', BitBangingSpiDevice::PI_BAD_USER_GPIO);
                break;
            case BitBangingSpiDevice::PI_NOT_SPI_GPIO:
                throw new TransferFailedException('Cross transfer failed (internal library error) => no SPI action in progress on this pin (PI_NOT_SPI_GPIO)', BitBangingSpiDevice::PI_NOT_SPI_GPIO);
                break;
            default:
                throw new TransferFailedException('Cross transfer failed => unknown error', $response->getResponse());
                break;
        }
    }

    /**
     * @param Response $response
     *
     * @return mixed
     */
    public function handleOpen(Response $response)
    {
        switch ($response->getResponse()) {
            case BitBangingSpiDevice::PI_BAD_USER_GPIO:
                throw new OpeningDeviceFailedException('Opening device failed => bad GPIO pin given (PI_BAD_USER_GPIO)', BitBangingSpiDevice::PI_BAD_USER_GPIO);
                break;
            case BitBangingSpiDevice::PI_BAD_SPI_BAUD:
                throw new OpeningDeviceFailedException('Opening device failed => bad baud rate given (PI_BAD_SPI_BAUD)', BitBangingSpiDevice::PI_BAD_SPI_BAUD);
                break;
            case BitBangingSpiDevice::PI_GPIO_IN_USE:
                throw new OpeningDeviceFailedException('Opening device failed => GPIO pin is already in use (PI_GPIO_IN_USE)', BitBangingSpiDevice::PI_GPIO_IN_USE);
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
        switch ($response->getResponse()) {
            case BitBangingSpiDevice::PI_BAD_USER_GPIO:
                throw new ClosingDeviceFailedException('Closing device failed (internal library error) => bad GPIO pin given (PI_BAD_USER_GPIO)', BitBangingSpiDevice::PI_BAD_USER_GPIO);
                break;
            case BitBangingSpiDevice::PI_NOT_SPI_GPIO:
                throw new ClosingDeviceFailedException('Closing device failed (internal library error) => no SPI action in progress on this pin (PI_NOT_SPI_GPIO)', BitBangingSpiDevice::PI_NOT_SPI_GPIO);
                break;
            default:
                throw new ClosingDeviceFailedException('Closing device failed => unknown error', $response->getResponse());
                break;
        }
    }
}