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
    Server\Disk
};
use Innmind\Url\Path;
use Innmind\Immutable\Maybe;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, new Logger(
            $this->createMock(Server::class),
            new NullLogger,
        ));
    }

    public function testCpu()
    {
        $inner = $this->createMock(Server::class);
        $inner
            ->expects($this->once())
            ->method('cpu')
            ->willReturn($cpu = Maybe::just(new Cpu(
                new Cpu\Percentage(1),
                new Cpu\Percentage(1),
                new Cpu\Percentage(1),
                new Cpu\Cores(1),
            )));

        $server = new Logger($inner, new NullLogger);

        $this->assertEquals($cpu, $server->cpu());
    }

    public function testMemory()
    {
        $inner = $this->createMock(Server::class);
        $inner
            ->expects($this->once())
            ->method('memory')
            ->willReturn($memory = Maybe::just(new Memory(
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
            )));

        $server = new Logger($inner, new NullLogger);

        $this->assertEquals($memory, $server->memory());
    }

    public function testProcesses()
    {
        $server = new Logger(
            $this->createMock(Server::class),
            new NullLogger,
        );

        $this->assertInstanceOf(Processes\LoggerProcesses::class, $server->processes());
    }

    public function testLoadAverage()
    {
        $inner = $this->createMock(Server::class);
        $inner
            ->expects($this->once())
            ->method('loadAverage')
            ->willReturn($loadAverage = new LoadAverage(1, 5, 15));

        $server = new Logger($inner, new NullLogger);

        $this->assertSame($loadAverage, $server->loadAverage());
    }

    public function testDisk()
    {
        $server = new Logger(
            $this->createMock(Server::class),
            new NullLogger,
        );

        $this->assertInstanceOf(Disk\LoggerDisk::class, $server->disk());
    }

    public function testTmp()
    {
        $inner = $this->createMock(Server::class);
        $inner
            ->expects($this->once())
            ->method('tmp')
            ->willReturn($tmp = Path::of('/tmp/'));

        $server = new Logger($inner, new NullLogger);

        $this->assertSame($tmp, $server->tmp());
    }
}
