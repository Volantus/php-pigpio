# Raw requests
Requests and Responses may be used for directly communicating with the Pigpio daemon.
More details (full command list) please consult the [Pigpio documentation](http://abyz.me.uk/rpi/pigpio/sif.html)

## Example
Starting a PWM signal on GPIO pin 21 with pulse width of 1500 microseconds:
```php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;

$client = new Client();
$response = $client->sendRaw(new DefaultRequest(Commands::SERVO, 21, 1500, 0));

$response->isSuccessful(); // was successful?
$response->getResponse();  // some responses return data
```

## Request types
Depending on the command, different requests types need to be used:
### Default request
Suitable for most requests, if no extension (data) is required by the command.
Default requests take up to two parameters:
```php
$response = $client->sendRaw(new DefaultRequest(Commands::WRITE, 16, 1, 0));
```
### Extended requests
Some requests require additional data in special format.
See extension column of [Pigpio documentation](http://abyz.me.uk/rpi/pigpio/sif.html) for more details.
Data format of the extension needs to be proved as [pack() syntax](http://php.net/manual/de/function.pack.php).
P3 parameter, which is holding the data length, is computed automatically.

Example: Sending a high pulse on GPIO 12 for 50 microseconds:
```php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\ExtensionRequest;

$client = new Client();
$response = $client->sendRaw(new ExtensionRequest(Commands::TRIG, 12, 50, 'L', [1]));
```

## Response structure
Depending on the command, different response structure types need to be attached to the request:
### Default response
Default response return data as integer. Negative values are interpreted as failure.
```php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\DefaultResponseStructure;

$client = new Client();
$responseStructure = new DefaultResponseStructure();
$response = $client->sendRaw(new DefaultRequest(Commands::WRITE, 16, 1, 0, $responseStructure));

$response->isSuccessful(); // was successful
$response->getResponse();  // some responses return data (P3)
```

### Unsigned response
This special type of commands return always positive return values and are always successful.
Reading GPIO 0-31:
```php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\UnsignedResponseStructure;

$client = new Client();
$responseStructure =  new UnsignedResponseStructure();
$response = $client->sendRaw(new DefaultRequest(Commands::BR1, 0, 0, 0, $responseStructure));

$response->isSuccessful(); // always true
$response->getResponse();  // hexadecimal bits of GPIO Pin 0-31
```

### Extended response
Some commands return more just an integer return value.
As the extension format is depending on the command, the extension format need to be specified as [unpack() syntax](http://php.net/manual/de/function.unpack.php) format
Reading SPI signal with length of 4 bytes:
```php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Protocol\Commands;
use Volantus\Pigpio\Protocol\DefaultRequest;
use Volantus\Pigpio\Protocol\ExtensionResponseStructure;

$client = new Client();
$responseStructure =  new ExtensionResponseStructure('C0\C1\C2\C3');
$response = $client->sendRaw(new DefaultRequest(Commands::SPIR, 1, 4, 0, $responseStructure));

$response->isSuccessful(); // always true
$response->getResponse();  // Length of the extension (4 in our case)
$response->getExtension(); // Array with 4 integer elements
```