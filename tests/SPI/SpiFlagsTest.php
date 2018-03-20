<?php
namespace Volantus\Pigpio\Tests\SPI;

use PHPUnit\Framework\TestCase;
use Volantus\Pigpio\SPI\SpiFlags;

/**
 * Class SpiFlagsTest
 *
 * @package Volantus\Pigpio\Tests\SPI
 */
class SpiFlagsTest extends TestCase
{
    public function test_mode_notSet_correct()
    {
        $flags = new SpiFlags();
        self::assertEquals(0, $flags->getFlags());
    }

    public function test_mode_0_correct()
    {
        $flags = new SpiFlags(['mode' => 0]);
        self::assertEquals(0, $flags->getFlags());
    }

    public function test_mode_1_correct()
    {
        $flags = new SpiFlags(['mode' => 1]);
        self::assertEquals(1, $flags->getFlags());
    }

    public function test_mode_2_correct()
    {
        $flags = new SpiFlags(['mode' => 2]);
        self::assertEquals(2, $flags->getFlags());
    }

    public function test_mode_3_correct()
    {
        $flags = new SpiFlags(['mode' => 3]);
        self::assertEquals(3, $flags->getFlags());
    }

    public function test_activeHigh_empty_correct()
    {
        $flags = new SpiFlags([
            'mode'       => 3,
            'activeHigh' => []
        ]);
        self::assertEquals(3, $flags->getFlags());
    }

    public function test_activeHigh_0_correct()
    {
        $flags = new SpiFlags([
            'mode'       => 3,
            'activeHigh' => [0]
        ]);
        self::assertEquals(7, $flags->getFlags());
    }

    public function test_activeHigh_1_correct()
    {
        $flags = new SpiFlags([
            'mode'       => 3,
            'activeHigh' => [1]
        ]);
        self::assertEquals(11, $flags->getFlags());
    }

    public function test_activeHigh_2_correct()
    {
        $flags = new SpiFlags([
            'mode'       => 3,
            'activeHigh' => [2]
        ]);
        self::assertEquals(19, $flags->getFlags());
    }

    public function test_activeHigh_0_2_correct()
    {
        $flags = new SpiFlags([
            'mode'       => 3,
            'activeHigh' => [0, 2]
        ]);
        self::assertEquals(23, $flags->getFlags());
    }

    public function test_notReserved_empty_correct()
    {
        $flags = new SpiFlags([
            'mode'       => 2,
            'activeHigh' => []
        ]);
        self::assertEquals(2, $flags->getFlags());
    }

    public function test_notReserved_0_correct()
    {
        $flags = new SpiFlags([
            'mode'        => 2,
            'notReserved' => [0]
        ]);
        self::assertEquals(34, $flags->getFlags());
    }

    public function test_notReserved_1_correct()
    {
        $flags = new SpiFlags([
            'mode'        => 2,
            'notReserved' => [1]
        ]);
        self::assertEquals(66, $flags->getFlags());
    }

    public function test_notReserved_2_correct()
    {
        $flags = new SpiFlags([
            'mode'        => 2,
            'notReserved' => [2]
        ]);
        self::assertEquals(130, $flags->getFlags());
    }

    public function test_notReserved_0_2_correct()
    {
        $flags = new SpiFlags([
            'mode'        => 2,
            'notReserved' => [0, 2]
        ]);
        self::assertEquals(162, $flags->getFlags());
    }

    public function test_auxDevice_false()
    {
        $flags = new SpiFlags([
            'mode'            => 1,
            'auxiliaryDevice' => false
        ]);
        self::assertEquals(1, $flags->getFlags());
    }

    public function test_auxDevice_true()
    {
        $flags = new SpiFlags([
            'mode'            => 1,
            'auxiliaryDevice' => true
        ]);
        self::assertEquals(257, $flags->getFlags());
    }

    public function test_auxDevice_mosiFirstSignificationBit_false()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice'           => true,
            'mosiLeastSignificantFirst' => false
        ]);
        self::assertEquals(256, $flags->getFlags());
    }

    public function test_auxDevice_mosiFirstSignificationBit_true()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice'           => true,
            'mosiLeastSignificantFirst' => true
        ]);
        self::assertEquals(16640, $flags->getFlags());
    }

    public function test_auxDevice_false_mosiFirstSignificationBit_ignored()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice'           => false,
            'mosiLeastSignificantFirst' => true
        ]);
        self::assertEquals(0, $flags->getFlags());
    }

    public function test_auxDevice_misoFirstSignificationBit_false()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice'           => true,
            'misoLeastSignificantFirst' => false
        ]);
        self::assertEquals(256, $flags->getFlags());
    }

    public function test_auxDevice_misoFirstSignificationBit_true()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice'           => true,
            'misoLeastSignificantFirst' => true
        ]);
        self::assertEquals(33024, $flags->getFlags());
    }

    public function test_auxDevice_false_misoFirstSignificationBit_ignored()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice'           => false,
            'misoLeastSignificantFirst' => true
        ]);
        self::assertEquals(0, $flags->getFlags());
    }

    public function test_auxDevice_wordSize_16()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice' => true,
            'wordSize'        => 16
        ]);
        self::assertEquals(1048832, $flags->getFlags());
    }

    public function test_auxDevice_false_wordSize_ignored()
    {
        $flags = new SpiFlags([
            'auxiliaryDevice' => false,
            'wordSize'        => 16
        ]);
        self::assertEquals(0, $flags->getFlags());
    }

    public function test_threeWire_false()
    {
        $flags = new SpiFlags([
            'activeHigh' => [1],
            'threeWire'  => false
        ]);
        self::assertEquals(8, $flags->getFlags());
    }

    public function test_threeWire_true()
    {
        $flags = new SpiFlags([
            'activeHigh' => [1],
            'threeWire'  => true
        ]);
        self::assertEquals(520, $flags->getFlags());
    }

    public function test_threeWire_alternateCount_0()
    {
        $flags = new SpiFlags([
            'threeWire'                 => true,
            'threeWireAlternatingCount' => 0
        ]);
        self::assertEquals(512, $flags->getFlags());
    }

    public function test_threeWire_alternateCount_14()
    {
        $flags = new SpiFlags([
            'threeWire'                 => true,
            'threeWireAlternatingCount' => 14
        ]);
        self::assertEquals(14848, $flags->getFlags());
    }

    public function test_threeWire_false_alternateCount_ignored()
    {
        $flags = new SpiFlags([
            'threeWire'                 => false,
            'threeWireAlternatingCount' => 14
        ]);
        self::assertEquals(0, $flags->getFlags());
    }
}