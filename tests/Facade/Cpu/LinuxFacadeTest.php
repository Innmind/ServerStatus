<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Facade\Cpu\LinuxFacade,
    Server\Cpu,
    Exception\CpuUsageNotAccessible,
};
use PHPUnit\Framework\TestCase;

class LinuxFacadeTest extends TestCase
{
    public function testInterface()
    {
        if (\PHP_OS !== 'Linux') {
            return;
        }

        $facade = new LinuxFacade;

        $this->assertInstanceOf(Cpu::class, $facade());
    }

    public function testThrowWhenProcessFails()
    {
        if (\PHP_OS === 'Linux') {
            return;
        }

        $this->expectException(CpuUsageNotAccessible::class);

        (new LinuxFacade)();
    }
}
