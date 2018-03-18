<?php
namespace Volantus\Pigpio\SPI;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\ExtensionRequest;

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
}