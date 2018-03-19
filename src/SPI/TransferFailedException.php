<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Protocol\Response;

/**
 * Class TransferFailedException
 *
 * @package Volantus\Pigpio\SPI
 */
class TransferFailedException extends \RuntimeException
{
    /**
     * @param Response $response
     *
     * @return TransferFailedException
     */
    public static function createForReadOperation(Response $response): self
    {
        switch ($response->getResponse()) {
            case RegularSpiDevice::PI_BAD_HANDLE:
                return new static('Reading from SPI device failed => bad handle (PI_BAD_HANDLE)', RegularSpiDevice::PI_BAD_HANDLE);
                break;
            case RegularSpiDevice::PI_BAD_SPI_COUNT:
                return new static('Reading from SPI device failed => bad count given (PI_BAD_SPI_COUNT)', RegularSpiDevice::PI_BAD_SPI_COUNT);
                break;
            case RegularSpiDevice::PI_SPI_XFER_FAILED:
                return new static('Reading from SPI device failed => data transfer failed (PI_SPI_XFER_FAILED)', RegularSpiDevice::PI_SPI_XFER_FAILED);
                break;
            default:
                return new static('Reading from SPI device failed => unknown error', $response->getResponse());
        }
    }
}