<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status;

use Innmind\Server\Status\{
    ServerFactory,
    Server,
    EnvironmentPath,
    Exception\UnsupportedOperatingSystem
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class ServerFactoryTest extends TestCase
{
    public function testMake()
    {
        $this->assertInstanceOf(Server::class, ServerFactory::build(
            new Clock,
            Control::build(
                new Clock,
                Streams::fromAmbientAuthority(),
                new Usleep,
            ),
            EnvironmentPath::of(\getenv('PATH')),
        ));
    }

    public function testThrowWhenUnsupportedOS()
    {
        if (\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->assertTrue(true);

            return;
        }

        $this->expectException(UnsupportedOperatingSystem::class);

        ServerFactory::build(new Clock, Control::build(
            new Clock,
            Streams::fromAmbientAuthority(),
            new Usleep,
            EnvironmentPath::of(\getenv('PATH')),
        ));
    }
}
