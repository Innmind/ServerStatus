<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status;

use Innmind\Server\Status\{
    ServerFactory,
    Server,
    Exception\UnsupportedOperatingSystem
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class ServerFactoryTest extends TestCase
{
    public function testMake()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $this->assertInstanceOf(Server::class, ServerFactory::build(
            new Clock,
            Control::build(
                new Clock,
                Streams::fromAmbientAuthority(),
                new Usleep,
            ),
            Map::of(),
        ));
    }

    public function testThrowWhenUnsupportedOS()
    {
        if (\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $this->expectException(UnsupportedOperatingSystem::class);

        ServerFactory::build(new Clock, Control::build(
            new Clock,
            Streams::fromAmbientAuthority(),
            new Usleep,
            Map::of(),
        ));
    }
}
