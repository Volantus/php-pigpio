<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\ExtensionRequest;
use Volantus\Pigpio\Protocol\ExtensionResponseStructure;
use Volantus\Pigpio\Protocol\Request;

/**
 * Class BitBaningSpiDevice
 *
 * @package Volantus\Pigpio\SPI
 */
class BitBaningSpiDevice extends SpiDevice
{
    const PI_BAD_SPI_BAUD  = -141;
    const PI_BAD_USER_GPIO = -2;
    const PI_GPIO_IN_USE   = -50;
    const PI_NOT_SPI_GPIO  = -142;

    /**
     * @var int
     */
    private $csPin;

    /**
     * @var int
     */
    private $misoPin;

    /**
     * @var int
     */
    private $mosiPin;

    /**
     * @var int
     */
    private $sclkPin;

    /**
     * BitBaningSpiDevice constructor.
     *
     * @param Client        $client
     * @param int           $baudRate      Baud speed (32K-125M, values above 30M are unlikely to work)
     * @param int           $csPin         GPIO pin (0-31) for CS
     * @param int           $misoPin       GPIO pin (0-31) for MISO
     * @param int           $mosiPin       GPIO pin (0-31) for MOSI
     * @param int           $sclkPin       GPIO pin (0-31) for SCLK
     * @param SpiFlags|null $flags         Optional flags
     * @param ErrorHandler  $errorHandler
     */
    public function __construct(Client $client, int $baudRate, int $csPin, int $misoPin, int $mosiPin, int $sclkPin, SpiFlags $flags = null, ErrorHandler $errorHandler = null)
    {
        parent::__construct($client, $baudRate, $errorHandler ?: new BitBangingDeviceErrorHandler(), $flags);

        $this->csPin = $csPin;
        $this->misoPin = $misoPin;
        $this->mosiPin = $mosiPin;
        $this->sclkPin = $sclkPin;
    }

    /**
     * @return Request
     */
    protected function getOpenRequest(): Request
    {
        return new ExtensionRequest(Commands::BSPIO, $this->csPin, 0, 'LLLLL', [
            $this->misoPin,
            $this->mosiPin,
            $this->sclkPin,
            $this->baudRate,
            $this->flags
        ]);
    }

    /**
     * @return Request
     */
    protected function getCloseRequest(): Request
    {
        return new DefaultRequest(Commands::BSPIC, $this->csPin, 0);
    }

    /**
     * @param array $data
     *
     * @return Request
     */
    protected function getCrossTransferRequest(array $data): Request
    {
        return new ExtensionRequest(Commands::BSPIX, $this->csPin, 0, 'C*', $data, new ExtensionResponseStructure('C*'));
    }

    /**
     * @return int
     */
    public function getCsPin(): int
    {
        return $this->csPin;
    }

    /**
     * @return int
     */
    public function getMisoPin(): int
    {
        return $this->misoPin;
    }

    /**
     * @return int
     */
    public function getMosiPin(): int
    {
        return $this->mosiPin;
    }

    /**
     * @return int
     */
    public function getSclkPin(): int
    {
        return $this->sclkPin;
    }
}