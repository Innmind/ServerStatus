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
use Innmind\Immutable\{
    Set,
    Maybe,
};
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;

class LoggerProcessesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, new LoggerProcesses(
            $this->createMock(Processes::class),
            new NullLogger,
        ));
    }

    public function testAll()
    {
        $inner = $this->createMock(Processes::class);
        $inner
            ->expects($this->once())
            ->method('all')
            ->willReturn($all = Set::of());

        $processes = new LoggerProcesses($inner, new NullLogger);

        $this->assertSame($all, $processes->all());
    }

    public function testGet()
    {
        $inner = $this->createMock(Processes::class);
        $inner
            ->expects($this->once())
            ->method('get')
            ->willReturn($process = Maybe::just(new Process(
                new Pid(1),
                new User('root'),
                new Percentage(1),
                new Memory(1),
                Maybe::just((new Clock)->now()),
                new Command('sleep 42'),
            )));

        $processes = new LoggerProcesses($inner, new NullLogger);

        $this->assertEquals($process, $processes->get(new Pid(1)));
    }
}
