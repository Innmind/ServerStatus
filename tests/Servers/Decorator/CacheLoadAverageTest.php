<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Servers\Decorator;

use Innmind\Server\Status\{
    Servers\Decorator\CacheLoadAverage,
    Server,
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Cpu\Cores,
    Server\Memory,
    Server\Memory\Bytes,
    Server\LoadAverage,
    Server\Processes,
    Server\Disk,
};
use Innmind\TimeContinuum\{
    Clock,
    Earth\ElapsedPeriod,
    PointInTime,
};
use PHPUnit\Framework\TestCase;

class CacheLoadAverageTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Server::class,
            new CacheLoadAverage(
                $this->createMock(Server::class),
                $this->createMock(Clock::class),
                new ElapsedPeriod(0)
            )
        );
    }

    public function testCpu()
    {
        $decorator = new CacheLoadAverage(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(Clock::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->once())
            ->method('cpu')
            ->willReturn($expected = new Cpu(new Percentage(33), new Percentage(33), new Percentage(34), new Cores(1)));
        $clock
            ->expects($this->never())
            ->method('now');

        $this->assertSame($expected, $decorator->cpu());
    }

    public function testMemory()
    {
        $decorator = new CacheLoadAverage(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(Clock::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->once())
            ->method('memory')
            ->willReturn($expected = new Memory(
                new Bytes(42),
                new Bytes(42),
                new Bytes(42),
                new Bytes(42),
                new Bytes(42),
                new Bytes(42)
            ));
        $clock
            ->expects($this->never())
            ->method('now');

        $this->assertSame($expected, $decorator->memory());
    }

    public function testProcesses()
    {
        $decorator = new CacheLoadAverage(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(Clock::class),
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
        $decorator = new CacheLoadAverage(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(Clock::class),
            new ElapsedPeriod(42)
        );
        $server
            ->expects($this->exactly(2))
            ->method('loadAverage')
            ->will(
                $this->onConsecutiveCalls(
                    $load1 = new LoadAverage(1, 5, 15),
                    $load2 = new LoadAverage(1, 5, 15)
                )
            );
        $first = $this->createMock(PointInTime::class);
        $second = $this->createMock(PointInTime::class);
        $second
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($first)
            ->willReturn(new ElapsedPeriod(24));
        $third = $this->createMock(PointInTime::class);
        $third
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($first)
            ->willReturn(new ElapsedPeriod(50));
        $clock
            ->expects($this->exactly(3))
            ->method('now')
            ->will($this->onConsecutiveCalls($first, $second, $third));

        $this->assertSame($load1, $decorator->loadAverage()); //put in cache
        $this->assertSame($load1, $decorator->loadAverage()); //load cache
        $this->assertSame($load2, $decorator->loadAverage()); //stale
    }

    public function testDisk()
    {
        $decorator = new CacheLoadAverage(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(Clock::class),
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
