<?php
namespace Volantus\Pigpio\HallSensor\Rev;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\Notification\Notifier;

/**
 * Class Factory
 *
 * @package Volantus\Pigpio\HallSensor\Rev
 */
abstract class Factory
{
    /**
     * @param string $address
     * @param int    $port
     *
     * @return RevolutionCounter
     */
    public static function create($address = '127.0.0.1', $port = Client::DEFAULT_PIGPIO_PORT): RevolutionCounter
    {
        $client = new Client(new Socket($address, $port));
        $notifier = new Notifier($client);
        return new RevolutionCounter($notifier);
    }
}