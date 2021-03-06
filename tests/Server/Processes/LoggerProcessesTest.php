<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes\LoggerProcesses,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    Server\Process\User,
    Server\Process\Command,
    Server\Process\Memory,
    Server\Cpu\Percentage,
};
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\Immutable\Map;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class LoggerProcessesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, new LoggerProcesses(
            $this->createMock(Processes::class),
            $this->createMock(LoggerInterface::class),
        ));
    }

    public function testAll()
    {
        $inner = $this->createMock(Processes::class);
        $inner
            ->expects($this->once())
            ->method('all')
            ->willReturn($all = Map::of('int', Process::class));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $processes = new LoggerProcesses($inner, $logger);

        $this->assertSame($all, $processes->all());
    }

    public function testGet()
    {
        $inner = $this->createMock(Processes::class);
        $inner
            ->expects($this->once())
            ->method('get')
            ->willReturn($process = new Process(
                new Pid(1),
                new User('root'),
                new Percentage(1),
                new Memory(1),
                (new Clock)->now(),
                new Command('sleep 42'),
            ));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $processes = new LoggerProcesses($inner, $logger);

        $this->assertSame($process, $processes->get(new Pid(1)));
    }
}
