<?php
namespace Volantus\Pigpio\SPI;

require_once __DIR__ . '/SpiInterfacePolyfill.php';

use Volantus\BerrySpi\SpiInterface;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Request;

/**
 * Class SpiDevice
 *
 * @package Volantus\Pigpio\SPI
 */
abstract class SpiDevice implements SpiInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $handle;

    /**
     * @var int
     */
    protected $baudRate;

    /**
     * @var int
     */
    protected $flags;

    /**
     * @var ErrorHandler
     */
    protected $errorHandler;

    /**
     * @param Client        $client
     * @param int           $baudRate      Baud speed (32K-125M, values above 30M are unlikely to work)
     * @param ErrorHandler  $errorHandler
     * @param SpiFlags|null $flags         Optional flags
     */
    public function __construct(Client $client, int $baudRate, ErrorHandler $errorHandler, SpiFlags $flags = null)
    {
        $this->client = $client;
        $this->baudRate = $baudRate;
        $this->flags = $flags ? $flags->getFlags() : 0;
        $this->errorHandler = $errorHandler;
    }

    public function __destruct()
    {
        if ($this->isOpen()) {
            try {
                $this->close();
            } catch (\Throwable $e) {}
        }
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

        $request = $this->getOpenRequest();
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            $this->errorHandler->handleOpen($response);
        }

        $this->handle = $response->getResponse();
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

        $request = $this->getCrossTransferRequest($data);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            $this->errorHandler->handleTransfer($request, $response);
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

        $request = $this->getCloseRequest();
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            $this->errorHandler->handleClose($response);
        }

        $this->handle = null;
    }

    /**
     * @return Request
     */
    protected abstract function getOpenRequest(): Request;

    /**
     * @return Request
     */
    protected abstract function getCloseRequest(): Request;

    /**
     * @param array $data
     *
     * @return Request
     */
    protected abstract function getCrossTransferRequest(array $data): Request;


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
    public function getSpeed(): int
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

    /**
     * @param array $data
     *
     * @return array
     */
    public function transfer(array $data): array
    {
        return $this->crossTransfer($data);
    }

    /**
     * @return bool
     */
    public static function initialize(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function isInitialized(): bool
    {
        return true;
    }
}