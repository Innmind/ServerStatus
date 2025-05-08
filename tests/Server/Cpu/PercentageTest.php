<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Cpu;

use Innmind\Server\Status\{
    Server\Cpu\Percentage,
    Exception\OutOfBoundsPercentage,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class PercentageTest extends TestCase
{
    public function testInterface()
    {
        $percentage = new Percentage(42.24);

        $this->assertSame(42.24, $percentage->toFloat());
        $this->assertSame('42.24%', $percentage->toString());
    }

    public function testThrowWhenPercentageLowerThanZero()
    {
        $this->expectException(OutOfBoundsPercentage::class);

        new Percentage(-1);
    }
}
