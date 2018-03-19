<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\Request;
use Volantus\Pigpio\Protocol\Response;

/**
 * Class TransferFailedException
 *
 * @package Volantus\Pigpio\SPI
 */
class TransferFailedException extends \RuntimeException
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return TransferFailedException
     */
    public static function create(Request $request, Response $response): self
    {
        switch ($request->getCommand()) {
            case Commands::SPIR:
                $operation = 'Reading from SPI device';
                break;
            case Commands::SPIW:
                $operation = 'Writing to SPI device';
                break;
            default:
                $operation = 'Unknown operation';
        }

        switch ($response->getResponse()) {
            case RegularSpiDevice::PI_BAD_HANDLE:
                return new static($operation . ' failed => bad handle (PI_BAD_HANDLE)', RegularSpiDevice::PI_BAD_HANDLE);
                break;
            case RegularSpiDevice::PI_BAD_SPI_COUNT:
                return new static($operation . ' failed => bad count given (PI_BAD_SPI_COUNT)', RegularSpiDevice::PI_BAD_SPI_COUNT);
                break;
            case RegularSpiDevice::PI_SPI_XFER_FAILED:
                return new static($operation . ' failed => data transfer failed (PI_SPI_XFER_FAILED)', RegularSpiDevice::PI_SPI_XFER_FAILED);
                break;
            default:
                return new static($operation . ' failed => unknown error', $response->getResponse());
        }
    }
}