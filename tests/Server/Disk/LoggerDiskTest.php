<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server,
    Server\Disk,
    Server\Disk\Volume,
    Server\Disk\Volume\MountPoint,
    ServerFactory,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\Time\{
    Clock,
    Halt,
};
use Innmind\IO\IO;
use Innmind\Immutable\Sequence;
use Psr\Log\NullLogger;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LoggerDiskTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Disk::class, Server::logger(
            $this->server(),
            new NullLogger,
        )->disk());
    }

    public function testVolumes()
    {
        $disk = Server::logger($this->server(), new NullLogger)->disk();

        $this->assertInstanceOf(Sequence::class, $disk->volumes());
    }

    public function testGet()
    {
        $disk = Server::logger($this->server(), new NullLogger)->disk();

        $this->assertInstanceOf(Volume::class, $disk->get(MountPoint::of('/'))->match(
            static fn($volume) => $volume,
            static fn() => null,
        ));
    }

    private function server(): Server
    {
        return ServerFactory::build(
            Clock::live(),
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Halt::new(),
            ),
            EnvironmentPath::of(\getenv('PATH')),
        );
    }
}
