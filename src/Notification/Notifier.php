<?php
namespace Volantus\Pigpio\Notification;

use Volantus\Pigpio\Client;
use Volantus\Pigpio\Notification\Event\EventFactory;
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
     * @var resource
     */
    private $pipeHandle;
    /**
     * @var null|EventFactory
     */
    private $factory;

    /**
     * Notifier constructor.
     *
     * @param Client            $client
     * @param string            $pipeBase
     * @param EventFactory|null $factory
     */
    public function __construct(Client $client = null, string $pipeBase = '/dev/pigpio', EventFactory $factory = null)
    {
        $this->client = $client ?: new Client();
        $this->pipeBase = $pipeBase;
        $this->factory = $factory ?: new EventFactory();
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
        // Already open?
        if ($this->handle !== null) {
            return;
        }

        $request = new DefaultRequest(Commands::NO, 0, 0);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw new OpeningFailedException('Failed receiving notification handle (Error: ' . $response->getResponse() . ')', $response->getResponse());
        }

        $this->handle = $response->getResponse();

        $pipeHandle = @fopen($this->pipeBase . $this->handle, 'r');
        if ($pipeHandle === false) {
            throw new OpeningFailedException('Failed to open file handle to pipe ' . $this->pipeBase . $this->handle);
        }

        $this->pipeHandle = $pipeHandle;
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

        // Already started?
        if ($this->callback !== null && !$this->paused) {
            return;
        }

        if (!is_resource($this->pipeHandle)) {
            throw new BrokenPipeException('File handle to pipe is invalid');
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
        // Already paused or not even started?
        if ($this->callback === null || $this->paused) {
            return;
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
        // Is notifier even open?
        if ($this->handle === null) {
            return;
        }

        $request = new DefaultRequest(Commands::NC, $this->handle, 0);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            throw new CancelFailedException('Failed canceling notification (Error: ' . $response->getResponse() . ')', $response->getResponse());
        }

        $this->paused = false;
        $this->callback = null;
        $this->handle = null;
        fclose($this->pipeHandle);
        $this->pipeHandle = null;
    }

    /**
     * Checks for new notification and calls the callback
     *
     * @param bool $blocking
     */
    public function tick(bool $blocking = false)
    {
        if ($this->callback === null) {
            throw new NotStartedException('Notifier needs to be started first');
        }

        for ($data = $this->readBlock($blocking); $data !== ''; $data = $this->readBlock(false)) {
            $event = $this->factory->decode($data);
            call_user_func($this->callback, $event);
        }
    }

    /**
     * @param bool $blocking
     *
     * @return string
     */
    private function readBlock(bool $blocking): string
    {
        stream_set_blocking($this->pipeHandle, $blocking);
        return fread($this->pipeHandle, 12);
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