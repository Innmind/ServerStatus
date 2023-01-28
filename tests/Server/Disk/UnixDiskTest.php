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
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class UnixDiskTest extends TestCase
{
    private $disk;

    public function setUp(): void
    {
        $this->disk = new UnixDisk(
            Control::build(
                new Clock,
                Streams::fromAmbientAuthority(),
                new Usleep,
            )->processes(),
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Disk::class, $this->disk);
    }

    public function testVolumes()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $volumes = $this->disk->volumes();

        $this->assertInstanceOf(Set::class, $volumes);
        $this->assertNotEmpty($volumes);
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
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $volume = $this
            ->disk
            ->get(new MountPoint('/'))
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

    public function testReturnEmptyListWhenInfoNotAccessible()
    {
        if (\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $this->assertEmpty($this->disk->volumes());
    }
}
