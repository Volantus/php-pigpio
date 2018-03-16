# Pigpio client library for PHP
[![Latest Stable Version](https://img.shields.io/packagist/v/volantus/php-pigpio.svg)](https://packagist.org/packages/volantus/php-pigpio)

PHP client for the great [Pigpio daemon](http://abyz.me.uk/rpi/pigpio/pigpiod.html) library

:bangbang: Alpha status:

> Library is currently in Alpha status. Bugs may occur, library may be unstable, API may be changed anytime.

> Feedback and bug reports are highly welcome!

## Installation
Library may be installed by using [Composer](https://getcomposer.org/):
```
composer require volantus/php-pigpio
```

## Features
Currently the following features are fully implemented.
If your desired Pigpio feature is not fully supported, you may use [raw requests](https://github.com/Volantus/php-pigpio/blob/master/docs/raw-requests.md).

| Feature                                                                                                       | Status                             | Version |
|---------------------------------------------------------------------------------------------------------------|------------------------------------|---------|
| [Notifications (e.g. GPIO changes)](https://github.com/Volantus/php-pigpio/blob/master/docs/notifications.md) | :heavy_check_mark: Fully supported | 0.1.1   |
| [Reading PWM signals](https://github.com/Volantus/php-pigpio/blob/master/docs/reading-pwm-signals.md)         | :heavy_check_mark: Fully supported | 0.1.2   |
| [Raw requests/response handling](https://github.com/Volantus/php-pigpio/blob/master/docs/raw-requests.md)     | :heavy_check_mark: Fully supported | 0.1.1   |

## Basic usage
This library is interacting with Pigpio by using sockets. The core communication is handled by the Client class:
```php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;

$client = new Client(new Socket('127.0.0.1', 8888));
```
### Starting the Pigpio daemon
If the daemon is not running already, it can simply be started via:
```bash
sudo pigpiod
```

## Contribution
Contributors are highly welcome!
Please report bugs, ask questions and create feature requests :)

### Pull requests
Three simple rules:
1. Write Tests
2. Follow the project code style
3. Use feature branches/forks and pull requests