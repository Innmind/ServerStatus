<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\Server\{
    Disk\Volume,
    Disk\Volume\MountPoint,
    Disk\Volume\Usage,
    Memory\Bytes,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class VolumeTest extends TestCase
{
    public function testInterface()
    {
        $volume = Volume::of(
            $mount = MountPoint::of('/'),
            $size = new Bytes(42),
            $available = new Bytes(42),
            $used = new Bytes(42),
            $usage = Usage::maybe(100)->match(
                static fn($usage) => $usage,
                static fn() => throw new \Exception('Should be valid'),
            ),
        );

        $this->assertSame($mount, $volume->mountPoint());
        $this->assertSame($size, $volume->size());
        $this->assertSame($available, $volume->available());
        $this->assertSame($used, $volume->used());
        $this->assertSame($usage, $volume->usage());
    }
}
