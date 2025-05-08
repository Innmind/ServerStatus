<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Memory,
    Memory\Bytes,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{
    public function testInterface()
    {
        $memory = new Memory(
            $total = Bytes::of(42),
            $active = Bytes::of(42),
            $free = Bytes::of(42),
            $swap = Bytes::of(42),
            $used = Bytes::of(42),
        );

        $this->assertSame($total, $memory->total());
        $this->assertSame($active, $memory->active());
        $this->assertSame($free, $memory->free());
        $this->assertSame($swap, $memory->swap());
        $this->assertSame($used, $memory->used());
    }
}
