# SPI communication
[SPI](https://en.wikipedia.org/wiki/Serial_Peripheral_Interface_Bus) is an serial bus protocol, which may be used for communication with external devices. 


## Setup
Communication is handled by an child of class SpiDevice. Either [regular](#regular-device) or [bit banging device](#bit-banging-device) might be used.
````php
use Volantus\Pigpio\Client;
use Volantus\Pigpio\SPI\RegularSpiDevice;

$client = new Client();
$device = new RegularSpiDevice($client, 0, 32000);
$device->open();
````
#### Cross transferring data
This is the usually the most common used transfer method.

This method transfers a given array of bytes and reads simultaneously the same amount of data (byte count) from the device.
The returned array is the data read from the device. Each array items contains a single byte.

:bangbang: Valid data range:
> Each array item need to be an unsigned byte (0 - 255);

````php
$receivedData = $device->crossTransfer([16, 8, 32]);
echo $receivedData[0];
echo $receivedData[1];
echo $receivedData[2];
````

#### Reading data
Reads the given amount of bytes from the device.

````php
$receivedData = $device->read(2);
echo $receivedData[0];
echo $receivedData[1];
````

#### Writing data
Writes the given date to the device.

:bangbang: Valid data range:
> Each array item need to be an unsigned byte (0 - 255);

````php
$receivedData = $device->write([8, 16, 200]);
````

## Device types
Pigpio supports two different operations modes:

|                                   | Regular device                       | Bit banging device                      |
|-----------------------------------|--------------------------------------|-----------------------------------------|
| PHP Class                         | Volantus\Pigpio\SPI\RegularSpiDevice | Volantus\Pigpio\SPI\BitBangingSpiDevice |
| Supports cross transfer?          | :heavy_check_mark:                   | :heavy_check_mark:                      |
| Supports write-only transfer?     | :heavy_check_mark:                   | :x:                                     |
| Supports read-only transfer?      | :heavy_check_mark:                   | :x:                                     |
| Max. parallel (slave) SPI devices | 2                                    | > 20 [(GPIO pin count) - 3]             |
| Max. baud speed                   | 125M (30M)                           | 250k                                    |

### Regular device
This communication modes uses the native GPIO SPI pins (GPIO 7 - 11).
Channel 0 = GPIO08, Channel 1 = GPIO07.

### Bit banging device
The communication uses any normal GPIO pins.
 
## Flags
Optionally the SpiDevice constructor accepts an SpiFlags helper object. 
Flags are optional and may be used to configure special SPI parameters.

For full details please consult the [official Pigpio documentation](http://abyz.me.uk/rpi/pigpio/cif.html#spiOpen).

The `SpiFlags` constructor accepts an associative array. If an key is not set the default value will be used.

Example: Switching to aux mode and setting the word size to 16 bytes:
````php
use Volantus\Pigpio\SPI\SpiFlags;

$spiFlags = new SpiFlags([
    'auxiliaryDevice' => true,
    'wordSize'        => 16
]);
````

### Available options:
| Option                    | Allowed values                                           | Description                                                                                                                                                          | Default                          |
|---------------------------|----------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------|
| mode                      | 0 - 3                                                    | SPI mode (modes 1 and 3 do not appear to work on the auxiliary device)                                                                                               | 0                                |
| activeHigh                | Array with max. 3 elements Allowed element value: 1 - 2  | Switches the given slave select pins to active high mode                                                                                                             | Active low for all CS/CE pins    |
| notReserved               | Array with max. 3 elements Allowed element value: 1 - 2  | Specified slave select (CE) pins are not reserved for SPI                                                                                                            | All CE pins are reserved for SPI |
| auxiliaryDevice           | true or false                                            | False = standard SPI device, True = auxiliary device                                                                                                                 | False (default device)           |
| mosiLeastSignificantFirst | true or false                                            | Send the least significant bit first on MOSI. Auxiliary device only.                                                                                                 | False                            |
| misoLeastSignificantFirst | true or false                                            | Read the least significant bit first on MISO. Auxiliary device only.                                                                                                 | False                            |
| wordSize                  | 0 - 32                                                   | Bits per SPI word                                                                                                                                                    | 8                                |
| threeWire                 | true or false                                            | Is the device a three-wire system?                                                                                                                                   | False                            |
| threeWireAlternatingCount | 0 - 15                                                   | Defines the number of bytes (0-15) to write before switching the MOSI line to MISO to read data. This field is ignored if threeWire=false. Standard SPI device only. | Disabled (0)                     |