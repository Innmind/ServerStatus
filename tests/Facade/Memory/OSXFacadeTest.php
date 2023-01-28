<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\Memory\OSXFacade,
    Server\Memory,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class OSXFacadeTest extends TestCase
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
        if (\PHP_OS !== 'Darwin') {
            $this->markTestSkipped();
        }

        $facade = new OSXFacade($this->server->processes(), Map::of([
            'PATH',
            $_SERVER['PATH'],
        ]));

        $this->assertInstanceOf(Memory::class, $facade()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Darwin') {
            $this->markTestSkipped();
        }

        $facade = new OSXFacade($this->server->processes(), Map::of([
            'PATH',
            $_SERVER['PATH'],
        ]));

        $this->assertNull($facade()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }
}
