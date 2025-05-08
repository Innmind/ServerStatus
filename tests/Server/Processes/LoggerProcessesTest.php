<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes\LoggerProcesses,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    ServerFactory,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\IO\IO;
use Innmind\Immutable\Set;
use Psr\Log\NullLogger;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LoggerProcessesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, new LoggerProcesses(
            $this->processes(),
            new NullLogger,
        ));
    }

    public function testAll()
    {
        $processes = new LoggerProcesses($this->processes(), new NullLogger);

        $this->assertInstanceOf(Set::class, $processes->all());
    }

    public function testGet()
    {
        $processes = new LoggerProcesses($this->processes(), new NullLogger);

        $this->assertInstanceOf(Process::class, $processes->get(Pid::of(1))->match(
            static fn($process) => $process,
            static fn() => null,
        ));
    }

    private function processes(): Processes
    {
        return ServerFactory::build(
            Clock::live(),
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Usleep::new(),
            ),
            EnvironmentPath::of(\getenv('PATH')),
        )->processes();
    }
}
