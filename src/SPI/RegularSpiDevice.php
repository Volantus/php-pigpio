<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\ExtensionRequest;
use Volantus\Pigpio\Protocol\ExtensionResponseStructure;
use Volantus\Pigpio\Protocol\Request;

/**
 * Class SpiInterface
 *
 * @package Volantus\Pigpio\SPI
 */
class RegularSpiDevice extends SpiDevice
{
    const PI_SPI_OPEN_FAILED = -73;
    const PI_BAD_SPI_SPEED   = -78;
    const PI_BAD_HANDLE      = -25;
    const PI_BAD_SPI_CHANNEL = -76;
    const PI_BAD_FLAGS       = -77;
    const PI_NO_AUX_SPI      = -91;
    const PI_BAD_SPI_COUNT   = -84;
    const PI_SPI_XFER_FAILED = -89;

    /**
     * @var int
     */
    private $channel;

    /**
     * RegularSpiDevice constructor.
     *
     * @param Client       $client
     * @param int          $channel       SPI channel (0 or 1)
     * @param int          $baudRate      Baud speed (32K-125M, values above 30M are unlikely to work)
     * @param int          $flags         Optional flags
     * @param ErrorHandler $errorHandler
     */
    public function __construct(Client $client, int $channel, int $baudRate, int $flags = 0, ErrorHandler $errorHandler = null)
    {
        parent::__construct($client, $baudRate, $flags, $errorHandler ?: new RegularDeviceErrorHandler());
        $this->channel = $channel;
    }

    /**
     * Reads given count of bytes from the SPI device
     * Returns one byte per array item
     *
     * @param int $count
     *
     * @return array
     */
    public function read(int $count): array
    {
        if ($this->handle === null) {
            throw new DeviceNotOpenException('Device needs to be opened first for reading');
        }

        $request = new DefaultRequest(Commands::SPIR, $this->handle, $count, new ExtensionResponseStructure('C*'));
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            $this->errorHandler->handleTransfer($request, $response);
        }

        return array_values($response->getExtension());
    }

    /**
     * Writes data to the SPI device
     *
     * @param array $data One unsigned byte (0 - 255) per array item
     */
    public function write(array $data)
    {
        if ($this->handle === null) {
            throw new DeviceNotOpenException('Device needs to be opened first for writing');
        }

        $request = new ExtensionRequest(Commands::SPIW, $this->handle, 0, 'C*', $data);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            $this->errorHandler->handleTransfer($request, $response);
        }
    }

    /**
     * @param array $data
     *
     * @return Request
     */
    protected function getCrossTransferRequest(array $data): Request
    {
        return new ExtensionRequest(Commands::SPIX, $this->handle, 0, 'C*', $data, new ExtensionResponseStructure('C*'));
    }

    /**
     * @return Request
     */
    protected function getOpenRequest(): Request
    {
        return new ExtensionRequest(Commands::SPIO, $this->channel, $this->baudRate, 'L', [$this->flags]);
    }

    /**
     * @return Request
     */
    protected function getCloseRequest(): Request
    {
        return new DefaultRequest(Commands::SPIC, $this->handle, 0);
    }
}