<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\LoadAverage;

use Innmind\Server\Status\{
    Facade\LoadAverage\CacheDecorator,
    Facade\LoadAverageFacade,
    Server\LoadAverage
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTimeInterface,
    ElapsedPeriod
};
use PHPUnit\Framework\TestCase;

class CacheDecoratorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            LoadAverageFacade::class,
            new CacheDecorator(
                $this->createMock(LoadAverageFacade::class),
                $this->createMock(TimeContinuumInterface::class),
                0
            )
        );
    }

    public function testFirstLoad()
    {
        $cache = new CacheDecorator(
            $facade = $this->createMock(LoadAverageFacade::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            42
        );
        $clock
            ->expects($this->once())
            ->method('now');
        $facade
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($expected = new LoadAverage(1, 2, 3));

        $this->assertSame($expected, $cache());
    }

    public function testLoadFromCache()
    {
        $cache = new CacheDecorator(
            $facade = $this->createMock(LoadAverageFacade::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            42
        );
        $clock
            ->expects($this->at(0))
            ->method('now')
            ->willReturn(
                $loadedAt = $this->createMock(PointInTimeInterface::class)
            );
        $clock
            ->expects($this->at(1))
            ->method('now')
            ->willReturn(
                $now = $this->createMock(PointInTimeInterface::class)
            );
        $now
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($loadedAt)
            ->willReturn(new ElapsedPeriod(30));
        $clock
            ->expects($this->exactly(2))
            ->method('now');
        $facade
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($expected = new LoadAverage(1, 2, 3));

        $this->assertSame($expected, $cache());
        $this->assertSame($expected, $cache());
    }

    public function testReloadFromFacade()
    {
        $cache = new CacheDecorator(
            $facade = $this->createMock(LoadAverageFacade::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            42
        );
        $clock
            ->expects($this->at(0))
            ->method('now')
            ->willReturn(
                $loadedAt = $this->createMock(PointInTimeInterface::class)
            );
        $clock
            ->expects($this->at(1))
            ->method('now')
            ->willReturn(
                $now = $this->createMock(PointInTimeInterface::class)
            );
        $now
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($loadedAt)
            ->willReturn(new ElapsedPeriod(50));
        $clock
            ->expects($this->exactly(3))
            ->method('now');
        $facade
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturn($expected = new LoadAverage(1, 2, 3));

        $this->assertSame($expected, $cache());
        $this->assertSame($expected, $cache());
    }

    public function testCacheAfterReload()
    {
        $cache = new CacheDecorator(
            $facade = $this->createMock(LoadAverageFacade::class),
            $clock = $this->createMock(TimeContinuumInterface::class),
            42
        );
        $clock
            ->expects($this->at(0))
            ->method('now')
            ->willReturn(
                $loadedAt = $this->createMock(PointInTimeInterface::class)
            );
        $clock
            ->expects($this->at(1))
            ->method('now')
            ->willReturn(
                $now = $this->createMock(PointInTimeInterface::class)
            );
        $now
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($loadedAt)
            ->willReturn(new ElapsedPeriod(50));
        $clock
            ->expects($this->at(2))
            ->method('now')
            ->willReturn(
                $atReload = $this->createMock(PointInTimeInterface::class)
            );
        $clock
            ->expects($this->at(3))
            ->method('now')
            ->willReturn(
                $afterReload = $this->createMock(PointInTimeInterface::class)
            );
        $afterReload
            ->expects($this->once())
            ->method('elapsedSince')
            ->with($atReload)
            ->willReturn(new ElapsedPeriod(30));
        $clock
            ->expects($this->exactly(4))
            ->method('now');
        $facade
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->willReturn($expected = new LoadAverage(1, 2, 3));

        $this->assertSame($expected, $cache());
        $this->assertSame($expected, $cache());
        $this->assertSame($expected, $cache());
    }
}
