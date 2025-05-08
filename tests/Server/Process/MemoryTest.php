<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\Memory;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{
    public function testInterface()
    {
        $memory = Memory::maybe(42.24)->match(
            static fn($memory) => $memory,
            static fn() => null,
        );

        $this->assertNotNull($memory);
        $this->assertSame(42.24, $memory->toFloat());
        $this->assertSame('42.24%', $memory->toString());
    }

    public function testReturnNothingWhenMemoryLowerThanZero()
    {
        $this->assertNull(Memory::maybe(-1)->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenMemoryHigherThanHundred()
    {
        $this->assertNull(Memory::maybe(101)->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }
}
