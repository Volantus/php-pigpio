# Reading PWM signals
php-pigpio supports measuring rotation speed of hall sensors like A3144, OH3144, AH3144E, TO-92U, etc. This feature is based on [notifications](https://github.com/Volantus/php-pigpio/blob/master/docs/notifications.md).

## Setup
Reading/calculation is handled by class `RevolutionCounter`, which requires a dedicated `Notifier` instance.
For easier setup, static `Factory` my be used.

Every time a signal is retrieved callback is called with an `RotationSpeed` object as parameter.

Measuring rotation speed on GPIO pin 22:
````php
use Volantus\Pigpio\HallSensor\Rev\Factory;
use Volantus\Pigpio\HallSensor\Rev\RotationSpeed;

$counter = Factory::create();
$counter->start(22, function (RotationSpeed $speed) {
    echo "Speed: " . round($speed->getRpm()) . " 1/min \n";
});

while (true) {
    $counter->tick(true);
}
````

## Ticks
To retrieve signals `$counter->tick()` needs to be called.
Every time `$notifier->tick()` is called, it will trigger the given callback with all signals measured since last call to `$counter->tick()`.

Either blocking or non-blocking mode is supported. More details: [Notification: Ticks](https://github.com/Volantus/php-pigpio/blob/master/docs/notifications.md#ticks)

