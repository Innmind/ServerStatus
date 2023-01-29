<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\Memory\LinuxFacade,
    Server\Memory,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use PHPUnit\Framework\TestCase;

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
            $this->markTestSkipped();
        }

        $facade = new LinuxFacade($this->server->processes());

        $this->assertInstanceOf(Memory::class, $facade()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }

    public function testReturnNohingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Linux') {
            $this->markTestSkipped();
        }

        $this->assertNull((new LinuxFacade($this->server->processes()))()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }
}
