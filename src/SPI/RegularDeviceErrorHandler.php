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
    public function handle(Request $request, Response $response)
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
}