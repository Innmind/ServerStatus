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
use Innmind\Immutable\{
    Set,
    Maybe,
};
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
            ->willReturn($all = Set::of());
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
            ->willReturn($volume = Maybe::just(new Volume(
                new MountPoint('/'),
                new Bytes(1),
                new Bytes(1),
                new Bytes(1),
                new Usage(1),
            )));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $disk = new LoggerDisk($inner, $logger);

        $this->assertEquals($volume, $disk->get(new MountPoint('/')));
    }
}
