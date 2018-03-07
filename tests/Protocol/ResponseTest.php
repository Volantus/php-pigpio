<?php
namespace Volantus\Pigpio\Protocol\Tests;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Protocol\Response;

/**
 * Class ResponseTest
 *
 * @package Volantus\Pigpio\Protocol\Tests
 */
class ResponseTest extends TestCase
{
    public function test_isSuccessful_zero_true()
    {
        $response = new Response(0);
        self::assertTrue($response->isSuccessful());
    }

    public function test_isSuccessful_greaterThenZero_true()
    {
        $response = new Response(1);
        self::assertTrue($response->isSuccessful());
    }

    public function test_isSuccessful_LessThenZero_false()
    {
        $response = new Response(-1);
        self::assertFalse($response->isSuccessful());
    }
}