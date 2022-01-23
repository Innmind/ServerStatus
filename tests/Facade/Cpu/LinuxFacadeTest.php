<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Facade\Cpu\LinuxFacade,
    Server\Cpu,
};
use PHPUnit\Framework\TestCase;

class LinuxFacadeTest extends TestCase
{
    public function testInterface()
    {
        if (\PHP_OS !== 'Linux') {
            $this->markTestSkipped();
        }

        $facade = new LinuxFacade;

        $this->assertInstanceOf(Cpu::class, $facade()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Linux') {
            $this->markTestSkipped();
        }

        $this->assertNull((new LinuxFacade)()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }
}
