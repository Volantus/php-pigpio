<?php
namespace Volantus\Pigpio\Protocol\Tests;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\Protocol\Bitmap;

/**
 * Class BitmapTest
 *
 * @package Volantus\Pigpio\Protocol\Tests
 */
class BitmapTest extends TestCase
{
    public function test_encode_correct()
    {
        $bitmap = new Bitmap([16, 20, 8]);
        self::assertEquals(1114368, $bitmap->encode());
    }

    public function test_encode_empty()
    {
        $bitmap = new Bitmap([]);
        self::assertEquals(0, $bitmap->encode());
    }
}