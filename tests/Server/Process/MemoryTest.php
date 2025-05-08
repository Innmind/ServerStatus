<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\{
    Server\Process\Memory,
    Exception\OutOfBoundsPercentage,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{
    public function testInterface()
    {
        $memory = new Memory(42.24);

        $this->assertSame(42.24, $memory->toFloat());
        $this->assertSame('42.24%', $memory->toString());
    }

    public function testThrowWhenMemoryLowerThanZero()
    {
        $this->expectException(OutOfBoundsPercentage::class);

        new Memory(-1);
    }

    public function testThrowWhenMemoryHigherThanHundred()
    {
        $this->expectException(OutOfBoundsPercentage::class);

        new Memory(101);
    }
}
