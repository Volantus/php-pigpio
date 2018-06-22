<?php
namespace Volantus\Pigpio\PWM;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;

/**
 * Class PwmSender
 *
 * @package Volantus\Pigpio\PWM
 */
class PwmSender
{
    /**
     * @var Client
     */
    private $client;

    /**
     * PwmSender constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * Sets the pulse width of the PWM signal
     *
     * @param int $gpioPin    GPIO pin (0-31)
     * @param int $pulseWidth Pulse with in microseconds (usually between 1000 and 2000)
     *
     * @throws CommandFailedException
     */
    public function setPulseWidth(int $gpioPin, int $pulseWidth)
    {
        $request = new DefaultRequest(Commands::SERVO, $gpioPin, $pulseWidth);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw new CommandFailedException('SERVO command failed with status code ' . $response->getResponse());
        }
    }
}