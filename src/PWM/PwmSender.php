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
    const PI_BAD_USER_GPIO  = -2;
    const PI_BAD_PULSEWIDTH = -7;
    const PI_BAD_DUTYCYCLE  = -8;
    const PI_BAD_DUTYRANGE  = -21;
    const PI_NOT_PERMITTED  = -41;

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
            switch ($response->getResponse()) {
                case self::PI_BAD_USER_GPIO:
                    throw new CommandFailedException('SERVO command failed => bad GPIO pin given (status code ' . $response->getResponse() . ')');
                case self::PI_BAD_PULSEWIDTH:
                    throw new CommandFailedException('SERVO command failed => given pulse width is out of valid range (status code ' . $response->getResponse() . ')');
                case self::PI_NOT_PERMITTED:
                    throw new CommandFailedException('SERVO command failed => operation was not permitted (status code ' . $response->getResponse() . ')');
                default:
                    throw new CommandFailedException('SERVO command failed with status code ' . $response->getResponse());
            }
        }
    }

    /**
     * Sets the duty cycle of the PWM signal
     *
     * @param int $gpioPin    GPIO pin (0-31)
     * @param int $dutyCycle  0 - range (default 255), while 0 is stopping the PWM signal
     *                        s. getRange() and setRange() methods
     *
     * @throws CommandFailedException
     */
    public function setDutyCycle(int $gpioPin, int $dutyCycle)
    {
        $request = new DefaultRequest(Commands::PWM, $gpioPin, $dutyCycle);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            switch ($response->getResponse()) {
                case self::PI_BAD_USER_GPIO:
                    throw new CommandFailedException('PWM command failed => bad GPIO pin given (status code ' . $response->getResponse() . ')');
                case self::PI_BAD_DUTYCYCLE:
                    throw new CommandFailedException('PWM command failed => given dutycycle is out of valid range (status code ' . $response->getResponse() . ')');
                case self::PI_NOT_PERMITTED:
                    throw new CommandFailedException('PWM command failed => operation was not permitted (status code ' . $response->getResponse() . ')');
                default:
                    throw new CommandFailedException('PWM command failed with status code ' . $response->getResponse());
            }
        }
    }

    /**
     * Sets the "sampling" range
     * The real range internally used depends on the frequency
     *
     * @param int $gpioPin GPIO pin (0-31)
     * @param int $range   25-40000
     *
     * @throws CommandFailedException
     */
    public function setRange(int $gpioPin, int $range)
    {
        $request = new DefaultRequest(Commands::PRS, $gpioPin, $range);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            switch ($response->getResponse()) {
                case self::PI_BAD_USER_GPIO:
                    throw new CommandFailedException('PRS command failed => bad GPIO pin given (status code ' . $response->getResponse() . ')');
                case self::PI_BAD_DUTYRANGE:
                    throw new CommandFailedException('PRS command failed => given range is not valid (status code ' . $response->getResponse() . ')');
                default:
                    throw new CommandFailedException('PRS command failed with status code ' . $response->getResponse());
            }
        }
    }

    /**
     * Set the frequency (in Hz) of the PWM to be used on the GPIO.
     *
     * @param int $gpioPin    GPIO pin (0-31)
     * @param int $frequency  >=0 Hz
     *                        The selectable frequencies depend upon the sample rate (follow link for details)
     *
     * @throws CommandFailedException
     * @see http://abyz.me.uk/rpi/pigpio/pdif2.html#set_PWM_frequency
     */
    public function setFrequency(int $gpioPin, int $frequency)
    {
        $request = new DefaultRequest(Commands::PFS, $gpioPin, $frequency);
        $response = $this->client->sendRaw($request);

        if (!$response->isSuccessful()) {
            switch ($response->getResponse()) {
                case self::PI_BAD_USER_GPIO:
                    throw new CommandFailedException('PFS command failed => bad GPIO pin given (status code ' . $response->getResponse() . ')');
                case self::PI_NOT_PERMITTED:
                    throw new CommandFailedException('PFS command failed => operation was not permitted (status code ' . $response->getResponse() . ')');
                default:
                    throw new CommandFailedException('PFS command failed with status code ' . $response->getResponse());
            }
        }
    }
}