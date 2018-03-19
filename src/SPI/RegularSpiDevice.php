<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\ExtensionRequest;
use Volantus\Pigpio\Protocol\ExtensionResponseStructure;

/**
 * Class SpiInterface
 *
 * @package Volantus\Pigpio\SPI
 */
class RegularSpiDevice
{
    const PI_BAD_SPI_CHANNEL = -76;
    const PI_BAD_SPI_SPEED   = -78;
    const PI_BAD_FLAGS       = -77;
    const PI_NO_AUX_SPI      = -91;
    const PI_SPI_OPEN_FAILED = -73;
    const PI_BAD_HANDLE      = -25;
    const PI_BAD_SPI_COUNT   = -84;
    const PI_SPI_XFER_FAILED = -89;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $handle;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Opens the SPI device (fetches a handle)
     *
     * @param int $channel   SPI channel (0 or 1)
     * @param int $baudRate  Baud speed (32K-125M, values above 30M are unlikely to work)
     * @param int $flags     Optional flags
     */
    public function open(int $channel, int $baudRate, int $flags = 0)
    {
        // Already open?
        if ($this->handle !== null) {
            return;
        }

        $request = new ExtensionRequest(Commands::SPIO, $channel, $baudRate, 'L', [$flags]);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw OpeningDeviceFailedException::create($response);
        }

        $this->handle = $response->getResponse();
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
            throw TransferFailedException::createForReadOperation($response);
        }

        return array_values($response->getExtension());
    }

    /**
     * Closes the SPI device (frees the handle)
     */
    public function close()
    {
        if ($this->handle === null) {
            return;
        }

        $request = new DefaultRequest(Commands::SPIC, $this->handle, 0);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw ClosingDeviceFailedException::create($response);
        }

        $this->handle = null;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->handle !== null;
    }
}