<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Servers\OSX,
    Server,
    Server\Cpu,
    Server\Memory,
    Server\LoadAverage,
    Server\Processes,
    Server\Disk,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;

class OSXTest extends TestCase
{
    private $server;

    public function setUp(): void
    {
        $this->server = new OSX(
            new Clock,
            Control::build(
                new Clock,
                Streams::fromAmbientAuthority(),
                new Usleep,
            ),
            EnvironmentPath::of(\getenv('PATH')),
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, $this->server);
    }

    public function testCpu()
    {
        if (\PHP_OS !== 'Darwin') {
            $this->markTestSkipped();
        }

        $this->assertInstanceOf(
            Cpu::class,
            $this->server->cpu()->match(
                static fn($cpu) => $cpu,
                static fn() => null,
            ),
        );
    }

    public function testMemory()
    {
        if (\PHP_OS !== 'Darwin') {
            $this->markTestSkipped();
        }

        $this->assertInstanceOf(
            Memory::class,
            $this
                ->server
                ->memory()
                ->match(
                    static fn($memory) => $memory,
                    static fn() => null,
                ),
        );
    }

    public function testProcesses()
    {
        $this->assertInstanceOf(Processes::class, $this->server->processes());
    }

    public function testLoadAverage()
    {
        $this->assertInstanceOf(LoadAverage::class, $this->server->loadAverage());
    }

    public function testDisk()
    {
        $this->assertInstanceOf(Disk::class, $this->server->disk());
    }

    public function testTmp()
    {
        $this->assertInstanceOf(Path::class, $this->server->tmp());
        $this->assertSame(
            \rtrim(\sys_get_temp_dir(), '/').'/',
            $this->server->tmp()->toString(),
        );
    }
}
