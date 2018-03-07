<?php
namespace Volantus\Pigpio\Protocol\Tests;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Protocol\DefaultRequest;

/**
 * Class DefaultRequestTest
 *
 * @package Volantus\Pigpio\Protocol\Tests
 */
class DefaultRequestTest extends TestCase
{
    public function test_encode_correct()
    {
        $request = new DefaultRequest(8, 21, 1500);
        self::assertEquals(hex2bin('0800000015000000dc05000000000000'), $request->encode());
    }
}