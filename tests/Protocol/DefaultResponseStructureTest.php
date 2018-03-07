<?php
namespace Volantus\Pigpio\Protocol\Tests;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Protocol\Response;
use Volantus\Pigpio\Protocol\DefaultResponseStructure;

/**
 * Class DefaultResponseStructureTest
 *
 * @package Volantus\Pigpio\Protocol\Tests
 */
class DefaultResponseStructureTest extends TestCase
{
    public function test_decode_correct()
    {
        $structure = new DefaultResponseStructure();
        $result = $structure->decode(hex2bin('0800000015000000dc0500006affffff'));

        self::assertInstanceOf(Response::class, $result);
        self::assertEquals(-150, $result->getResponse());
    }
}