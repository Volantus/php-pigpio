<?php
namespace Volantus\Pigpio\Tests\Notification;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Notification\Event\AliveEvent;
use Volantus\Pigpio\Notification\Event\EventFactory;
use Volantus\Pigpio\Notification\Event\GpioEvent;
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\Notification\OpeningFailedException;
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
     * @var EventFactory|MockObject
     */
    private $factory;

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
        $this->factory = $this->getMockBuilder(EventFactory::class)->getMock();
        $this->notifier = new Notifier($this->client, $this->tmpDirectory . '/pigpio', $this->factory);
    }

    public function test_open_alreadyOpen()
    {
        $this->createPipe(1);

        $this->client->method('sendRaw')->willReturn(new Response(1));
        $this->notifier->open();
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
     * @expectedException \Volantus\Pigpio\Notification\OpeningFailedException
     */
    public function test_open_openingPipeFailed()
    {
        $this->expectExceptionMessage('Failed to open file handle to pipe ' . $this->tmpDirectory . '/pigpio15');

        $this->client->method('sendRaw')->willReturn(new Response(15));
        $this->notifier->open();
    }

    public function test_open_correctRequest()
    {
        $this->createPipe(1);

        $this->client->expects(self::once())
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NO, 0, 0)))
            ->willReturn(new Response(1));

        $this->notifier->open();
        self::assertTrue($this->notifier->isOpen());
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\HandleMissingException
     * @expectedExceptionMessage Notifier needs to be opened first
     */
    public function test_start_notOpened()
    {
        $this->notifier->start(new Bitmap([20]), function () {});
    }

    public function test_start_alreadyStarted()
    {
        $this->createPipe(41);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(0));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
        $this->notifier->start(new Bitmap([8]), function () {});

        self::assertTrue($this->notifier->isStarted());
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\BeginFailedException
     * @expectedExceptionMessage Failed starting notification (Error: -12)
     * @expectedExceptionCode -12
     */
    public function test_start_failure()
    {
        $this->createPipe(41);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->willReturn(new Response(-12));

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function () {});
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\BrokenPipeException
     * @expectedExceptionMessage File handle to pipe is invalid
     */
    public function test_start_brokenPipe()
    {
        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        try {
            $this->notifier->open();
        } catch (OpeningFailedException $e) {}
        $this->notifier->start(new Bitmap([20]), function () {});
    }

    public function test_start_correctRequest()
    {
        $this->createPipe(41);

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
        $this->createPipe(41);

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

    public function test_pause_notStarted()
    {
        $this->notifier->pause();

        self::assertFalse($this->notifier->isStarted());
        self::assertFalse($this->notifier->isStarted());
        self::assertFalse($this->notifier->isPaused());
    }

    public function test_pause_alreadyPaused()
    {
        $this->createPipe(41);

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

        self::assertFalse($this->notifier->isStarted());
        self::assertTrue($this->notifier->isPaused());
        self::assertTrue($this->notifier->isOpen());
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\PausingFailedException
     * @expectedExceptionMessage Failed pausing notification (Error: -8)
     */
    public function test_pause_failed()
    {
        $this->createPipe(41);

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
        $this->createPipe(41);

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

    public function test_cancel_notOpened()
    {
        $this->notifier->cancel();

        self::assertFalse($this->notifier->isOpen());
        self::assertFalse($this->notifier->isStarted());
        self::assertFalse($this->notifier->isPaused());
    }

    /**
     * @expectedException \Volantus\Pigpio\Notification\CancelFailedException
     * @expectedExceptionMessage Failed canceling notification (Error: -5)
     * @expectedExceptionCode -5
     */
    public function test_cancel_failed()
    {
        $this->createPipe(36);

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
        $this->createPipe(36);

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

    /**
     * @expectedException \Volantus\Pigpio\Notification\NotStartedException
     * @expectedExceptionMessage Notifier needs to be started first
     */
    public function test_tick_notStarted()
    {
        $this->notifier->tick();
    }

    public function test_tick_callbackCalled()
    {
        $this->createPipe(41);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NB, 41, 1048576)))
            ->willReturn(new Response(0));

        file_put_contents($this->tmpDirectory . '/pigpio41', pack('LLSS', 1, 2, 3, 4));

        $expected = new AliveEvent(4, 49461, []);
        $this->factory->expects(self::once())
            ->method('decode')
            ->with(self::equalTo(pack('LLSS', 1, 2, 3, 4)))
            ->willReturn($expected);

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function (GpioEvent $event) use (&$result) {
            $result = $event;
        });
        $this->notifier->tick();

        self::assertEquals($expected, $result);
    }

    public function test_tick_multipleBlocksInPipe()
    {
        $this->createPipe(41);

        $this->client->expects(self::at(0))
            ->method('sendRaw')
            ->willReturn(new Response(41));

        $this->client->expects(self::at(1))
            ->method('sendRaw')
            ->with(self::equalTo(new DefaultRequest(Commands::NB, 41, 1048576)))
            ->willReturn(new Response(0));

        file_put_contents($this->tmpDirectory . '/pigpio41', pack('LLSS', 1, 2, 3, 4));
        file_put_contents($this->tmpDirectory . '/pigpio41', pack('LLSS', 4, 5, 6, 7), FILE_APPEND);

        $expected = new AliveEvent(4, 49461, []);
        $this->factory->expects(self::at(0))
            ->method('decode')
            ->with(self::equalTo(pack('LLSS', 1, 2, 3, 4)))
            ->willReturn($expected);

        $this->factory->expects(self::at(1))
            ->method('decode')
            ->with(self::equalTo(pack('LLSS', 4, 5, 6, 7)))
            ->willReturn($expected);

        $this->notifier->open();
        $this->notifier->start(new Bitmap([20]), function (GpioEvent $event) {});
        $this->notifier->tick();
    }


    private function createPipe(int $handle)
    {
        $this->fileSystem->touch($this->tmpDirectory . '/pigpio' . $handle);
    }

    protected function tearDown()
    {
        $this->fileSystem->remove($this->tmpDirectory);
    }

}