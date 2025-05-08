<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Cpu,
    Cpu\Percentage,
    Cpu\Cores,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class CpuTest extends TestCase
{
    public function testInterface()
    {
        $cpu = new Cpu(
            $user = Percentage::maybe(31)->match(
                static fn($percentage) => $percentage,
                static fn() => throw new \Exception('Should be valid'),
            ),
            $system = Percentage::maybe(33)->match(
                static fn($percentage) => $percentage,
                static fn() => throw new \Exception('Should be valid'),
            ),
            $idle = Percentage::maybe(36)->match(
                static fn($percentage) => $percentage,
                static fn() => throw new \Exception('Should be valid'),
            ),
            $cores = Cores::of(4),
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
