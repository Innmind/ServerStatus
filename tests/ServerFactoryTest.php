<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status;

use Innmind\Server\Status\{
    ServerFactory,
    Server,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\IO\IO;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class ServerFactoryTest extends TestCase
{
    public function testMake()
    {
        $this->assertInstanceOf(Server::class, ServerFactory::build(
            Clock::live(),
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Usleep::new(),
            ),
            EnvironmentPath::of(\getenv('PATH')),
        ));
    }
}
