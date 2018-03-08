<?php
namespace Volantus\Pigpio\Tests\Notification;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\Protocol\Bitmap;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\Response;

/**
 * Class NotifierTest
 *
 * @package Volantus\Pigpio\Tests\Notification
 */
class NotifierTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $tmpDirectory;

    /**
     * @var Notifier
     */
    private $notifier;

    protected function setUp()
    {
        $this->tmpDirectory = sys_get_temp_dir() . '/' . uniqid();
        $this->fileSystem = new Filesystem();
        $this->fileSystem->mkdir($this->tmpDirectory);
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->notifier = new Notifier($this->client, $this->tmpDirectory . '/pigpio');
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\AlreadyOpenException
     * @expectedExceptionMessage Already fetched a handle, unable to open twice
     */
    public function test_open_alreadyOpen()
    {
        $this->client->method('sendRaw')->willReturn(new Response(1));
        $this->notifier->open();
        $this->notifier->open();
    }

    public function test_open_correctRequest()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NO, 0, 0)))
            ->willReturn(new Response(1));

        $this->notifier->open();
        self::assertTrue($this->notifier->isOpen());
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\OpeningFailedException
     * @expectedExceptionMessage Failed receiving notification handle (Error: -1)
     * @expectedExceptionCode -1
     */
    public function test_open_failed()
    {
        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NO, 0, 0)))
            ->willReturn(new Response(-1));

        $this->notifier->open();
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\HandleMissingException
     * @expectedExceptionMessage Notifier needs to be opened first
     */
    public function test_start_notOpened()
    {
        $this->notifier->start(new Bitmap([20]), function () {});
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\AlreadyStartedException
     * @expectedExceptionMessage Notification has been already started
     */
    public function test_start_alreadyStarted()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(0));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
        $this->notifier->start(new Bitmap([8]), function () {});
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\BeginFailedException
     * @expectedExceptionMessage Failed starting notification (Error: -12)
     * @expectedExceptionCode -12
     */
    public function test_start_failure()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(-12));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
    }

    public function test_start_correctRequest()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NB, 41, 1048576)))
            ->willReturn(new Response(0));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});

        self::assertTrue($this->notifier->isStarted());
        self::assertFalse($this->notifier->isPaused());
    }

    public function test_start_restartPaused()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NB, 41, 1048576)))
            ->willReturn(new Response(0));

        $this->client->expects(self::at(2))
            ->method('sendRaw')
            ->willReturn(new Response(0));

        $this->client->expects(self::at(3))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NB, 41, 1048576)))
            ->willReturn(new Response(0));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
        $this->notifier->pause();
        $this->notifier->start(new Bitmap([20]), function () {});

        self::assertTrue($this->notifier->isStarted());
        self::assertFalse($this->notifier->isPaused());
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\NotStartedException
     * @expectedExceptionMessage Notifier needs to be started first
     */
    public function test_pause_notStarted()
    {
        $this->notifier->pause();
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\AlreadyPausedException
     * @expectedExceptionMessage Unable to pause an already paused notification
     */
    public function test_pause_alreadyPaused()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(0));

        $this->client->expects(self::at(2))
            ->method('sendRaw')
            ->willReturn(new Response(0));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
        $this->notifier->pause();
        $this->notifier->pause();
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\PausingFailedException
     * @expectedExceptionMessage Failed pausing notification (Error: -8)
     */
    public function test_pause_failed()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(0));

        $this->client->expects(self::at(2))
            ->method('sendRaw')
            ->willReturn(new Response(-8));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
        $this->notifier->pause();
    }

    public function test_pause_correctRequest()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(0));

        $this->client->expects(self::at(2))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NP, 41, 0)))
            ->willReturn(new Response(0));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
        $this->notifier->pause();

        self::assertTrue($this->notifier->isPaused());
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\HandleMissingException
     * @expectedExceptionMessage Notifier needs to be opened first
     */
    public function test_cancel_notOpened()
    {
        $this->notifier->cancel();
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\CancelFailedException
     * @expectedExceptionMessage Failed canceling notification (Error: -5)
     * @expectedExceptionCode -5
     */
    public function test_cancel_failed()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(36));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(-5));

        $this->notifier->open();
        $this->notifier->cancel();
    }

    public function test_cancel_correctRequest()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(36));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NC, 36, 0)))
            ->willReturn(new Response(0));

        $this->notifier->open();
        $this->notifier->cancel();

        self::assertFalse($this->notifier->isOpen());
        self::assertFalse($this->notifier->isStarted());
        self::assertFalse($this->notifier->isPaused());
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->tmpDirectory);
    }

}