<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server\Disk\Logger,
    Server\Disk,
    Server\Disk\Volume,
    Server\Disk\Volume\MountPoint,
    ServerFactory,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt;
use Innmind\IO\IO;
use Innmind\Immutable\Set;
use Psr\Log\NullLogger;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LoggerDiskTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Disk::class, Logger::of(
            $this->disk(),
            new NullLogger,
        ));
    }

    public function testVolumes()
    {
        $disk = Logger::of($this->disk(), new NullLogger);

        $this->assertInstanceOf(Set::class, $disk->volumes());
    }

    public function testGet()
    {
        $disk = Logger::of($this->disk(), new NullLogger);

        $this->assertInstanceOf(Volume::class, $disk->get(MountPoint::of('/'))->match(
            static fn($volume) => $volume,
            static fn() => null,
        ));
    }

    private function disk(): Disk
    {
        return ServerFactory::build(
            Clock::live(),
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Halt::new(),
            ),
            EnvironmentPath::of(\getenv('PATH')),
        )->disk();
    }
}
