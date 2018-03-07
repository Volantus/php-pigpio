<?php
namespace Volantus\Pigpio\Protocol\Tests;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\Protocol\UnsignedResponseStructure;

/**
 * Class UnsignedResponseStructureTest
 *
 * @package Volantus\Pigpio\Protocol\Tests
 */
class UnsignedResponseStructureTest extends TestCase
{
    public function test_decode_correct()
    {
        $structure = new UnsignedResponseStructure();

        $result = $structure->decode(hex2bin('1a000000000000000000000002000000'));

        self::assertInstanceOf(Response::class, $result);
        self::assertEquals(2, $result->getResponse());
    }
}