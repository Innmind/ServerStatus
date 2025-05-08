<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server\Disk\UnixDisk,
    Server\Disk,
    Server\Disk\Volume,
    Server\Disk\Volume\MountPoint,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\IO\IO;
use Innmind\Immutable\Set;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class UnixDiskTest extends TestCase
{
    private $disk;

    public function setUp(): void
    {
        $this->disk = UnixDisk::of(
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Usleep::new(),
            )->processes(),
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Disk::class, $this->disk);
    }

    public function testVolumes()
    {
        $volumes = $this->disk->volumes();

        $this->assertInstanceOf(Set::class, $volumes);
        $this->assertGreaterThanOrEqual(1, $volumes->size());
        $this->assertTrue(
            $volumes
                ->find(static fn($volume) => $volume->mountPoint()->is('/'))
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
    }

    public function testGet()
    {
        $volume = $this
            ->disk
            ->get(MountPoint::of('/'))
            ->match(
                static fn($volume) => $volume,
                static fn() => null,
            );

        $this->assertInstanceOf(Volume::class, $volume);
        $this->assertSame('/', $volume->mountPoint()->toString());
        $this->assertTrue($volume->size()->toInt() > 0);
        $this->assertTrue($volume->available()->toInt() > 0);
        $this->assertTrue($volume->used()->toInt() > 0);
        $this->assertTrue($volume->usage()->toFloat() > 0);
    }
}
