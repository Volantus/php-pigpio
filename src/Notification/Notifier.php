<?php
namespace Volantus\Pigpio\Notification;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Bitmap;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;

/**
 * Class Notifier
 *
 * @package Volantus\Pigpio\Notification
 */
class Notifier
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $pipeBase;

    /**
     * @var int
     */
    private $handle;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var bool
     */
    private $paused = false;

    /**
     * Notifier constructor.
     *
     * @param Client $client
     * @param string $pipeBase
     */
    public function __construct(Client $client, string $pipeBase = '/dev/pigpio')
    {
        $this->client = $client;
        $this->pipeBase = $pipeBase;
    }

    public function __destruct()
    {
        if ($this->isOpen()) {
            try {
                $this->cancel();
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Opens a notification handle
     */
    public function open()
    {
        if ($this->handle !== null) {
            throw new AlreadyOpenException('Already fetched a handle, unable to open twice');
        }

        $request = new DefaultRequest(Commands::NO, 0, 0);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw new OpeningFailedException('Failed receiving notification handle (Error: ' . $response->getResponse() . ')', $response->getResponse());
        }

        $this->handle = $response->getResponse();
    }

    /**
     * Stars notification (needs to be opened first)
     *
     * @param Bitmap   $gpioPins
     * @param callable $callback
     */
    public function start(Bitmap $gpioPins, callable $callback)
    {
        if ($this->handle === null) {
            throw new HandleMissingException('Notifier needs to be opened first');
        }

        if ($this->callback !== null && !$this->paused) {
            throw new AlreadyStartedException('Notification has been already started');
        }

        $request = new DefaultRequest(Commands::NB, $this->handle, $gpioPins->encode());
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw new BeginFailedException('Failed starting notification (Error: ' . $response->getResponse() . ')', $response->getResponse());
        }

        $this->callback = $callback;
        $this->paused = false;
    }

    /**
     * Pauses the notification (needs to be opened + started fist)
     */
    public function pause()
    {
        if ($this->callback === null) {
            throw new NotStartedException('Notifier needs to be started first');
        }

        if ($this->paused) {
            throw new AlreadyPausedException('Unable to pause an already paused notification');
        }

        $request = new DefaultRequest(Commands::NP, $this->handle, 0);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw new PausingFailedException('Failed pausing notification (Error: ' . $response->getResponse() . ')', $response->getResponse());
        }

        $this->paused = true;
    }

    /**
     * Stops the notification completely and frees the handle (Need to be opened at least)
     */
    public function cancel()
    {
        if ($this->handle === null) {
            throw new HandleMissingException('Notifier needs to be opened first');
        }

        $request = new DefaultRequest(Commands::NC, $this->handle, 0);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw new CancelFailedException('Failed canceling notification (Error: ' . $response->getResponse() . ')', $response->getResponse());
        }

        $this->paused = false;
        $this->callback = null;
        $this->handle = null;
    }

    /**
     * Checks for new notification and calls the callback
     */
    public function tick()
    {
        // ToDo Implement pipe structure
    }

    /**
     * @return bool
     */
    public function isPaused(): bool
    {
        return $this->paused;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->callback !== null && !$this->paused;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->handle !== null;
    }
}