# Reading PWM signals
php-pigpio supports reading [PWM signals](https://en.wikipedia.org/wiki/Pulse-width_modulation). This feature is based on [notifications](https://github.com/Volantus/php-pigpio/blob/master/docs/notifications.md).

## Setup
Reading/Calculation is handled by PWM receiver class, which requires a dedicated Notifier instance. 

Every time a signal is retrieved callback is called with an PwmSignal object as parameter.

Reading PWM signals on GPIO pin 21:
````php
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\PWM\PwmReceiver;
use Volantus\Pigpio\PWM\PwmSignal;

$notifier = new Notifier();
$receiver = new PwmReceiver($notifier);
$receiver->start([21], function (PwmSignal $signal) {
    echo $signal->getPulseWidth(); // e.g. 1500
});
````
## Ticks
To retrieve signals `$notifier->tick()` needs to be called.
Every time `$notifier->tick()` is called, it will trigger your callback with all signals measured since last call to `$notifier->tick()`.

Either blocking or non-blocking mode is supported. More details: [Notification: Ticks](https://github.com/Volantus/php-pigpio/blob/master/docs/notifications.md#ticks)

## Multiple signals
Reading multiple signals in parallel on different GPIO pins is supported.

Simply specify all GPIO pins as first parameter of start method:
````php
$notifier = new Notifier();
$receiver = new PwmReceiver($notifier);
$receiver->start([8, 16, 8], function (PwmSignal $signal) {
    echo $signal->getGpioPin() . ': ' . $signal->getPulseWidth();
});
````
Example output:
> 8: 1264

> 16: 1679

> 18: 1800
