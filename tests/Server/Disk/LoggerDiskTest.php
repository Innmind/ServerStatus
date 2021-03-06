<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server\Disk\LoggerDisk,
    Server\Disk,
    Server\Disk\Volume,
    Server\Disk\Volume\MountPoint,
    Server\Disk\Volume\Usage,
    Server\Memory\Bytes,
};
use Innmind\Immutable\Map;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class LoggerDiskTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Disk::class, new LoggerDisk(
            $this->createMock(Disk::class),
            $this->createMock(LoggerInterface::class),
        ));
    }

    public function testVolumes()
    {
        $inner = $this->createMock(Disk::class);
        $inner
            ->expects($this->once())
            ->method('volumes')
            ->willReturn($all = Map::of('string', Volume::class));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $disk = new LoggerDisk($inner, $logger);

        $this->assertSame($all, $disk->volumes());
    }

    public function testGet()
    {
        $inner = $this->createMock(Disk::class);
        $inner
            ->expects($this->once())
            ->method('get')
            ->willReturn($volume = new Volume(
                new MountPoint('/'),
                new Bytes(1),
                new Bytes(1),
                new Bytes(1),
                new Usage(1),
            ));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $disk = new LoggerDisk($inner, $logger);

        $this->assertSame($volume, $disk->get(new MountPoint('/')));
    }
}
