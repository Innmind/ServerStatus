<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Servers\Logger,
    Server,
    Server\Cpu,
    Server\Memory,
    Server\LoadAverage,
    Server\Processes,
    Server\Disk,
    ServerFactory,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\TimeWarp\Halt;
use Innmind\IO\IO;
use Innmind\Url\Path;
use Psr\Log\NullLogger;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, Logger::of(
            $this->server(),
            new NullLogger,
        ));
    }

    public function testCpu()
    {
        $server = Logger::of($this->server(), new NullLogger);

        $this->assertInstanceOf(Cpu::class, $server->cpu()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }

    public function testMemory()
    {
        $server = Logger::of($this->server(), new NullLogger);

        $this->assertInstanceOf(Memory::class, $server->memory()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }

    public function testProcesses()
    {
        $server = Logger::of(
            $this->server(),
            new NullLogger,
        );

        $this->assertInstanceOf(Processes\Logger::class, $server->processes());
    }

    public function testLoadAverage()
    {
        $server = Logger::of($this->server(), new NullLogger);

        $this->assertInstanceOf(LoadAverage::class, $server->loadAverage()->unwrap());
    }

    public function testDisk()
    {
        $server = Logger::of(
            $this->server(),
            new NullLogger,
        );

        $this->assertInstanceOf(Disk\Logger::class, $server->disk());
    }

    public function testTmp()
    {
        $server = Logger::of($this->server(), new NullLogger);

        $this->assertInstanceOf(Path::class, $server->tmp());
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
