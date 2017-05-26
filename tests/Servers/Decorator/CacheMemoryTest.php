<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Servers\Decorator;

use Innmind\Server\Status\{
    Servers\Decorator\CacheMemory,
    Server,
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Memory,
    Server\Memory\Bytes,
    Server\LoadAverage,
    Server\Processes,
    Server\Disk
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod,
    PointInTimeInterface
};
use PHPUnit\Framework\TestCase;

class CacheMemoryTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Server::class,
            new CacheMemory(
                $this->createMock(Server::class),
                $this->createMock(TimeContinuumInterface::class),
                new ElapsedPeriod(0)
            )
        );
    }

    public function testCpu()
    {
        $decorator = new CacheMemory(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->once())
            ->method('cpu')
            ->willReturn($expected = new Cpu(new Percentage(33), new Percentage(33), new Percentage(34)));
        $clock
            ->expects($this->never())
            ->method('now');

        $this->assertSame($expected, $decorator->cpu());
    }

    public function testMemory()
    {
        $decorator = new CacheMemory(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->exactly(2))
            ->method('memory')
            ->will(
                $this->onConsecutiveCalls(
                    $memory1 = new Memory(new Bytes(42), new Bytes(42), new Bytes(42), new Bytes(42), new Bytes(42), new Bytes(42)),
                    $memory2 = new Memory(new Bytes(42), new Bytes(42), new Bytes(42), new Bytes(42), new Bytes(42), new Bytes(42))
                )
            );
        $clock
            ->expects($this->at(0))
            ->method('now')
            ->willReturn(
                $first = $this->createMock(PointInTimeInterface::class)
            );
        $clock
            ->expects($this->at(1))
            ->method('now')
            ->willReturn(
                $second = $this->createMock(PointInTimeInterface::class)
            );
        $second
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($first)
            ->willReturn(new ElapsedPeriod(24));
        $clock
            ->expects($this->at(2))
            ->method('now')
            ->willReturn(
                $third = $this->createMock(PointInTimeInterface::class)
            );
        $third
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($first)
            ->willReturn(new ElapsedPeriod(50));
        $clock
            ->expects($this->exactly(3))
            ->method('now');

        $this->assertSame($memory1, $decorator->memory()); //put in cache
        $this->assertSame($memory1, $decorator->memory()); //load cache
        $this->assertSame($memory2, $decorator->memory()); //stale
    }

    public function testProcesses()
    {
        $decorator = new CacheMemory(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($expected = $this->createMock(Processes::class));
        $clock
            ->expects($this->never())
            ->method('now');

        $this->assertSame($expected, $decorator->processes());
    }

    public function testLoadAverage()
    {
        $decorator = new CacheMemory(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->once())
            ->method('loadAverage')
            ->willReturn($expected = new LoadAverage(1, 5, 15));
        $clock
            ->expects($this->never())
            ->method('now');

        $this->assertSame($expected, $decorator->loadAverage());
    }

    public function testDisk()
    {
        $decorator = new CacheMemory(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->once())
            ->method('disk')
            ->willReturn($expected = $this->createMock(Disk::class));
        $clock
            ->expects($this->never())
            ->method('now');

        $this->assertSame($expected, $decorator->disk());
    }
}
