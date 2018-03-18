<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Protocol\Response;

/**
 * Class OpeningDeviceFailed
 *
 * @package Volantus\Pigpio\SPI
 */
class OpeningDeviceFailedException extends \RuntimeException
{
    /**
     * @param Response $response
     *
     * @return OpeningDeviceFailedException
     */
    public static function create(Response $response): self
    {
        switch ($response->getResponse()) {
            case RegularSpiDevice::PI_BAD_SPI_CHANNEL:
                return new static('Opening device failed => bad SPI channel given (PI_BAD_SPI_CHANNEL)', RegularSpiDevice::PI_BAD_SPI_CHANNEL);
                break;
            case RegularSpiDevice::PI_BAD_FLAGS:
                return new static('Opening device failed => bad flags given (PI_BAD_FLAGS)', RegularSpiDevice::PI_BAD_FLAGS);
                break;
            case RegularSpiDevice::PI_NO_AUX_SPI:
                return new static('Opening device failed => no AUX available (PI_NO_AUX_SPI)', RegularSpiDevice::PI_NO_AUX_SPI);
                break;
            case RegularSpiDevice::PI_BAD_SPI_SPEED:
                return new static('Opening device failed => bad speed given (PI_BAD_SPI_SPEED)', RegularSpiDevice::PI_BAD_SPI_SPEED);
                break;
            case RegularSpiDevice::PI_SPI_OPEN_FAILED:
                return new static('Opening device failed (PI_SPI_OPEN_FAILED)', RegularSpiDevice::PI_SPI_OPEN_FAILED);
                break;
            default:
                return new static('Opening device failed => unknown error', $response->getResponse());
                break;
        }
    }
}