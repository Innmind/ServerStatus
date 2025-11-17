<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    ServerFactory,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt;
use Innmind\IO\IO;
use Innmind\Immutable\Sequence;
use Psr\Log\NullLogger;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LoggerProcessesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, Server::logger(
            $this->server(),
            new NullLogger,
        )->processes());
    }

    public function testAll()
    {
        $processes = Server::logger($this->server(), new NullLogger)->processes();

        $this->assertInstanceOf(Sequence::class, $processes->all());
    }

    public function testGet()
    {
        $processes = Server::logger($this->server(), new NullLogger)->processes();

        $this->assertInstanceOf(Process::class, $processes->get(Pid::of(1))->match(
            static fn($process) => $process,
            static fn() => null,
        ));
    }

    private function server(): Server
    {
        return ServerFactory::build(
            Clock::live(),
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Halt::new(),
            ),
            EnvironmentPath::of(\getenv('PATH')),
        );
    }
}
