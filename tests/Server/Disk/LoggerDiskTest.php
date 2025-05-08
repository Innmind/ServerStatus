<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server\Disk\LoggerDisk,
    Server\Disk,
    Server\Disk\Volume,
    Server\Disk\Volume\MountPoint,
    ServerFactory,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\Immutable\Set;
use Psr\Log\NullLogger;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LoggerDiskTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Disk::class, new LoggerDisk(
            $this->disk(),
            new NullLogger,
        ));
    }

    public function testVolumes()
    {
        $disk = new LoggerDisk($this->disk(), new NullLogger);

        $this->assertInstanceOf(Set::class, $disk->volumes());
    }

    public function testGet()
    {
        $disk = new LoggerDisk($this->disk(), new NullLogger);

        $this->assertInstanceOf(Volume::class, $disk->get(new MountPoint('/'))->match(
            static fn($volume) => $volume,
            static fn() => null,
        ));
    }

    private function disk(): Disk
    {
        return ServerFactory::build(
            new Clock,
            Control::build(
                new Clock,
                Streams::fromAmbientAuthority(),
                new Usleep,
            ),
            EnvironmentPath::of(\getenv('PATH')),
        )->disk();
    }
}
