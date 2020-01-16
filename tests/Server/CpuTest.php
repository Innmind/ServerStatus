<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Cpu,
    Cpu\Percentage,
    Cpu\Cores
};
use PHPUnit\Framework\TestCase;

class CpuTest extends TestCase
{
    public function testInterface()
    {
        $cpu = new Cpu(
            $user = new Percentage(31),
            $system = new Percentage(33),
            $idle = new Percentage(36),
            $cores = new Cores(4)
        );

        $this->assertSame($user, $cpu->user());
        $this->assertSame($system, $cpu->system());
        $this->assertSame($idle, $cpu->idle());
        $this->assertSame($cores, $cpu->cores());
        $this->assertSame(
            'CPU usage: 31% user, 33% sys, 36% idle',
            $cpu->toString(),
        );
    }
}
