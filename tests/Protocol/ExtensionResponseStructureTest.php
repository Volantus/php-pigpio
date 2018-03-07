<?php
namespace Volantus\Pigpio\Protocol\Tests;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Protocol\ExtensionResponseStructure;
use Volantus\Pigpio\Protocol\Response;

/**
 * Class ExtensionResponseStructureTest
 *
 * @package Volantus\Pigpio\Protocol\Tests
 */
class ExtensionResponseStructureTest extends TestCase
{
    public function test_decode_lengthCorrect()
    {
        $structure = new ExtensionResponseStructure('C*');
        $result = $structure->decode(hex2bin('3800000001000000040000000400000020204080'));

        self::assertInstanceOf(Response::class, $result);
        self::assertEquals(4, $result->getResponse());
    }

    public function test_decode_extensionCorrect()
    {
        $structure = new ExtensionResponseStructure('C*');
        $result = $structure->decode(hex2bin('3800000001000000040000000400000020204080'));

        self::assertInstanceOf(Response::class, $result);
        self::assertEquals([1 => 32, 2 => 32, 3 => 64, 4 => 128], $result->getExtension());
    }
}