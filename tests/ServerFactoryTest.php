<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status;

use Innmind\Server\Status\{
    ServerFactory,
    Server,
    Exception\UnsupportedOperatingSystem
};
use Innmind\TimeContinuum\Earth\Clock;
use PHPUnit\Framework\TestCase;

class ServerFactoryTest extends TestCase
{
    public function testMake()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $server = (new ServerFactory(new Clock))();

        $this->assertInstanceOf(Server::class, $server);
        $this->assertInstanceOf(Server::class, ServerFactory::build(new Clock));
    }

    public function testThrowWhenUnsupportedOS()
    {
        if (\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $this->expectException(UnsupportedOperatingSystem::class);

        (new ServerFactory(new Clock))();
    }
}
