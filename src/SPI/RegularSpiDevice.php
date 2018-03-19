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
     * @var int
     */
    private $channel;

    /**
     * @var int
     */
    private $baudRate;

    /**
     * @var int
     */
    private $flags;

    /**
     * @param Client $client
     * @param int    $channel  SPI channel (0 or 1)
     * @param int    $baudRate Baud speed (32K-125M, values above 30M are unlikely to work)
     * @param int    $flags    Optional flags
     */
    public function __construct(Client $client, int $channel, int $baudRate, int $flags = 0)
    {
        $this->client = $client;
        $this->channel = $channel;
        $this->baudRate = $baudRate;
        $this->flags = $flags;
    }

    /**
     * Opens the SPI device (fetches a handle)
     */
    public function open()
    {
        // Already open?
        if ($this->handle !== null) {
            return;
        }

        $request = new ExtensionRequest(Commands::SPIO, $this->channel, $this->baudRate, 'L', [$this->flags]);
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
            throw TransferFailedException::create($request, $response);
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
            throw TransferFailedException::create($request, $response);
        }
    }

    /**
     * Writes the given data to SPI device and read simultaneously the same amount (byte count) of data
     * Returns one (unsigned) byte per array item
     *
     * @param array $data
     *
     * @return array
     */
    public function crossTransfer(array $data): array
    {
        if ($this->handle === null) {
            throw new DeviceNotOpenException('Device needs to be opened first for cross transfer');
        }

        $request = new ExtensionRequest(Commands::SPIX, $this->handle, 0, 'C*', $data, new ExtensionResponseStructure('C*'));
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw TransferFailedException::create($request, $response);
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

    /**
     * @return int
     */
    public function getHandle(): int
    {
        return $this->handle;
    }

    /**
     * @return int
     */
    public function getChannel(): int
    {
        return $this->channel;
    }

    /**
     * @return int
     */
    public function getBaudRate(): int
    {
        return $this->baudRate;
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }
}