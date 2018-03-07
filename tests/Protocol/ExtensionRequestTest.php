<?php
namespace Volantus\Pigpio\Protocol\Tests;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Protocol\ExtensionRequest;

/**
 * Class ExtensionRequestTest
 *
 * @package Volantus\Pigpio\Protocol\Tests
 */
class ExtensionRequestTest extends TestCase
{
    public function test_encode_correct()
    {
        $request = new ExtensionRequest(37, 16, 200, 'L', [64]);
        self::assertEquals(hex2bin('2500000010000000c80000000400000040000000'), $request->encode());
    }
}