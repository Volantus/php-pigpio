# Notifications
Notifications may be used for recognizing state changes of GPIO pins or custom events.

## Setup
Receiving events for GPIO pin 21
````php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Notification\Event\GpioEvent;
use Volantus\Pigpio\Notification\Notifier;
use Volantus\Pigpio\Protocol\Bitmap;

$client = new Client();
$notifier = new Notifier($client);

$notifier->open();
$notifier->start(new Bitmap([21]), function (GpioEvent $event) {
    $pinStatus = $event->getGpioStatus()[0];
    echo "Pin 21 is " . $pinStatus->isHigh() ? 'High' : 'Low';
});

while (true) {
    $notifier->tick(true);
}

$notifier->cancel();
````
### No remote support
Library is using internally pipes, therefore Pigpio Deamon needs to run on the same system.

## Configure GPIO pins in scope
Pins of interest are specified as Bitmap Object.
Monitoring Pin 8, 12 and 16:
````php
$notifier->start(new Bitmap([8, 12, 16]), function (GpioEvent $event) {
    // Handling events
});
````

## Ticks
To receive new events `$notifier->tick()` methods need to be called. The method checks for events in the pipe, decodes them and fires the callback.
Ticks may be performed in blocking or non-blocking mode. 
### Blocking ticks
`$notifier->tick(true)`
If no event is in the pipe, it waits until the next event is retrieved, fires the callback and returns.
### Non-Blocking ticks
`$notifier->tick(false)`
If no event is in the pipe, it will immediately return without blocking.

## Event types
Four different types of events may be retrieved by the callback. All of them are extending class `Volantus\Pigpio\Notification\Event\GpioEvent`.

To identify which type of event was fired, you may check the class or use the abstract getter methods.

|                      | AliveEvent                                   | CustomEvent                                             | StateChangedEvent                                        | WatchdogEvent                                             |
|----------------------|----------------------------------------------|---------------------------------------------------------|----------------------------------------------------------|-----------------------------------------------------------|
| **Trigger**          | Gets fired, if 60sec no other event occured  | Custom event has been fired                             | State of at least one of the configured pins has changed | Timeout for GPIO pin occured                              |
| **Sequence ID**      | :heavy_check_mark:                           | :heavy_check_mark:                                      | :heavy_check_mark:                                       | :heavy_check_mark:                                        |
| **Ticks**            | :heavy_check_mark:                           | :heavy_check_mark:                                      | :heavy_check_mark:                                       | :heavy_check_mark:                                        |
| **GPIO Status**      | :heavy_check_mark:                           | :heavy_check_mark:                                      | :heavy_check_mark:                                       | :heavy_check_mark:                                        |
| **Special property** | -                                            | Event ID (0 - 31)                                       | -                                                        | GPIO ID of pin which timed out                            |
| **Identification**   | `->isAliveEvent()`                           | `->istCustomEvent()`                                    | `->isStateChangedEvent()`                                | ´->isTimeoutEvent()´                                      |
| **Setup supported?** | No setup required                            | :x: Firing custom event current not supported by client | :heavy_check_mark: Default event                         | :x: Setting up timeout, currently not supported by client |

#### Property: Sequence ID
Each event has a unique incrementing sequence ID.

#### Property: Ticks
Microseconds since boot.

:bangbang: Warning #1:

> Ticks are 32-Bit integer values, so counter will wrap around after ~1H 12min

:bangbang: Warning #2:

> Number may get negative on 32-Bit systems (conversion from signed to unsigned PHP)

#### Property: GPIO Status
Each event contains an array of GpioStatus objects. Each object is representing the status of one GPIO pin:
````php
$notifier->start(new Bitmap([16, 22]), function (GpioEvent $event) {
    $gpioPins = $event->getGpioStatus();
    echo $gpioPins[0]->getPin()   // 16
    echo $gpioPins[0]->isHigh()   // True
});
````

