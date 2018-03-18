<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Protocol\Response;

/**
 * Class ClosingDeviceFailedException
 *
 * @package Volantus\Pigpio\SPI
 */
class ClosingDeviceFailedException extends \RuntimeException
{
    /**
     * @param Response $response
     *
     * @return ClosingDeviceFailedException
     */
    public static function create(Response $response): self
    {
        switch ($response->getResponse()) {
            case RegularSpiDevice::PI_BAD_HANDLE:
                return new static('Closing SPI device failed => daemon responded that wrong handle was given (PI_BAD_HANDLE)', RegularSpiDevice::PI_BAD_HANDLE);
            default:
                return new static('Closing SPI device failed => unknown error', $response->getResponse());
        }
    }
}