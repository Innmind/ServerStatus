<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Facade\Cpu\LinuxFacade,
    Server\Cpu,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LinuxFacadeTest extends TestCase
{
    private $server;

    public function setUp(): void
    {
        $this->server = Control::build(
            new Clock,
            Streams::fromAmbientAuthority(),
            new Usleep,
        );
    }

    public function testInterface()
    {
        if (\PHP_OS !== 'Linux') {
            $this->assertTrue(true);

            return;
        }

        $facade = new LinuxFacade($this->server->processes());

        $this->assertInstanceOf(Cpu::class, $facade()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Linux') {
            $this->assertTrue(true);

            return;
        }

        $this->assertNull((new LinuxFacade($this->server->processes()))()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }
}
