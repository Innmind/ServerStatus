<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Memory,
    Memory\Bytes
};
use PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
{
    public function testInterface()
    {
        $memory = new Memory(
            $total = new Bytes(42),
            $wired = new Bytes(42),
            $active = new Bytes(42),
            $free = new Bytes(42),
            $swap = new Bytes(42),
            $used = new Bytes(42)
        );

        $this->assertSame($total, $memory->total());
        $this->assertSame($wired, $memory->wired());
        $this->assertSame($active, $memory->active());
        $this->assertSame($free, $memory->free());
        $this->assertSame($swap, $memory->swap());
        $this->assertSame($used, $memory->used());
    }
}
