<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\Memory\LinuxFacade,
    Server\Memory,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\IO\IO;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LinuxFacadeTest extends TestCase
{
    private $server;

    public function setUp(): void
    {
        $this->server = Control::build(
            Clock::live(),
            IO::fromAmbientAuthority(),
            Usleep::new(),
        );
    }

    public function testInterface()
    {
        if (\PHP_OS !== 'Linux') {
            $this->assertTrue(true);

            return;
        }

        $facade = new LinuxFacade($this->server->processes());

        $this->assertInstanceOf(Memory::class, $facade()->unwrap());
    }

    public function testReturnNohingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Linux') {
            $this->assertTrue(true);

            return;
        }

        $this->assertNull((new LinuxFacade($this->server->processes()))()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }
}
