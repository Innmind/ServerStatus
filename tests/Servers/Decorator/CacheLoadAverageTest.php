<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Servers\Decorator;

use Innmind\Server\Status\{
    Servers\Decorator\CacheLoadAverage,
    Server,
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Memory,
    Server\Memory\Bytes,
    Server\LoadAverage,
    Server\Processes
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod,
    PointInTimeInterface
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
                $this->createMock(TimeContinuumInterface::class),
                new ElapsedPeriod(0)
            )
        );
    }

    public function testCpu()
    {
        $decorator = new CacheLoadAverage(
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
        $decorator = new CacheLoadAverage(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
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
        $decorator = new CacheLoadAverage(
            $server = $this->createMock(Server::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
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

        $this->assertSame($load1, $decorator->loadAverage()); //put in cache
        $this->assertSame($load1, $decorator->loadAverage()); //load cache
        $this->assertSame($load2, $decorator->loadAverage()); //stale
    }
}
