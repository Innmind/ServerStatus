<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Facade\Cpu\OSXFacade,
    Server\Cpu,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\Time\{
    Clock,
    Halt,
};
use Innmind\IO\IO;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class OSXFacadeTest extends TestCase
{
    private $server;

    public function setUp(): void
    {
        $this->server = Control::build(
            Clock::live(),
            IO::fromAmbientAuthority(),
            Halt::new(),
        );
    }

    public function testInterface()
    {
        if (\PHP_OS !== 'Darwin') {
            $this->assertTrue(true);

            return;
        }

        $facade = new OSXFacade($this->server->processes());

        $this->assertInstanceOf(Cpu::class, $facade()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Darwin') {
            $this->assertTrue(true);

            return;
        }

        $this->assertNull((new OSXFacade($this->server->processes()))()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }
}
