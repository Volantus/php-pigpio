<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\Request;
use Volantus\Pigpio\Protocol\Response;

/**
 * Class RegularDeviceErrorHandler
 *
 * @package Volantus\Pigpio\SPI
 */
class RegularDeviceErrorHandler implements ErrorHandler
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return mixed
     */
    public function handleTransfer(Request $request, Response $response)
    {
        switch ($request->getCommand()) {
            case Commands::SPIR:
                $operation = 'Reading from SPI device';
                break;
            case Commands::SPIW:
                $operation = 'Writing to SPI device';
                break;
            case Commands::SPIX:
                $operation = 'SPI cross transfer';
                break;
            default:
                $operation = 'Unknown operation';
        }

        switch ($response->getResponse()) {
            case RegularSpiDevice::PI_BAD_HANDLE:
                throw new TransferFailedException($operation . ' failed => bad handle (PI_BAD_HANDLE)', RegularSpiDevice::PI_BAD_HANDLE);
                break;
            case RegularSpiDevice::PI_BAD_SPI_COUNT:
                throw new TransferFailedException($operation . ' failed => bad count given (PI_BAD_SPI_COUNT)', RegularSpiDevice::PI_BAD_SPI_COUNT);
                break;
            case RegularSpiDevice::PI_SPI_XFER_FAILED:
                throw new TransferFailedException($operation . ' failed => data transfer failed (PI_SPI_XFER_FAILED)', RegularSpiDevice::PI_SPI_XFER_FAILED);
                break;
            default:
                throw new TransferFailedException($operation . ' failed => unknown error', $response->getResponse());
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
            case RegularSpiDevice::PI_BAD_SPI_CHANNEL:
                throw new OpeningDeviceFailedException('Opening device failed => bad SPI channel given (PI_BAD_SPI_CHANNEL)', RegularSpiDevice::PI_BAD_SPI_CHANNEL);
                break;
            case RegularSpiDevice::PI_BAD_FLAGS:
                throw new OpeningDeviceFailedException('Opening device failed => bad flags given (PI_BAD_FLAGS)', RegularSpiDevice::PI_BAD_FLAGS);
                break;
            case RegularSpiDevice::PI_NO_AUX_SPI:
                throw new OpeningDeviceFailedException('Opening device failed => no AUX available (PI_NO_AUX_SPI)', RegularSpiDevice::PI_NO_AUX_SPI);
                break;
            case RegularSpiDevice::PI_BAD_SPI_SPEED:
                throw new OpeningDeviceFailedException('Opening device failed => bad speed given (PI_BAD_SPI_SPEED)', RegularSpiDevice::PI_BAD_SPI_SPEED);
                break;
            case RegularSpiDevice::PI_SPI_OPEN_FAILED:
                throw new OpeningDeviceFailedException('Opening device failed (PI_SPI_OPEN_FAILED)', RegularSpiDevice::PI_SPI_OPEN_FAILED);
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
            case RegularSpiDevice::PI_BAD_HANDLE:
                throw new ClosingDeviceFailedException('Closing SPI device failed => daemon responded that wrong handle was given (PI_BAD_HANDLE)', RegularSpiDevice::PI_BAD_HANDLE);
            default:
                throw new ClosingDeviceFailedException('Closing SPI device failed => unknown error', $response->getResponse());
        }
    }
}