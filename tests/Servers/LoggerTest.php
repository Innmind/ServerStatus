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
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, new Logger(
            $this->createMock(Server::class),
            $this->createMock(LoggerInterface::class),
        ));
    }

    public function testCpu()
    {
        $inner = $this->createMock(Server::class);
        $inner
            ->expects($this->once())
            ->method('cpu')
            ->willReturn($cpu = new Cpu(
                new Cpu\Percentage(1),
                new Cpu\Percentage(1),
                new Cpu\Percentage(1),
                new Cpu\Cores(1),
            ));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $server = new Logger($inner, $logger);

        $this->assertSame($cpu, $server->cpu());
    }

    public function testMemory()
    {
        $inner = $this->createMock(Server::class);
        $inner
            ->expects($this->once())
            ->method('memory')
            ->willReturn($memory = new Memory(
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
                new Memory\Bytes(1),
            ));
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $server = new Logger($inner, $logger);

        $this->assertSame($memory, $server->memory());
    }

    public function testProcesses()
    {
        $server = new Logger(
            $this->createMock(Server::class),
            $this->createMock(LoggerInterface::class),
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
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $server = new Logger($inner, $logger);

        $this->assertSame($loadAverage, $server->loadAverage());
    }

    public function testDisk()
    {
        $server = new Logger(
            $this->createMock(Server::class),
            $this->createMock(LoggerInterface::class),
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
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('debug');

        $server = new Logger($inner, $logger);

        $this->assertSame($tmp, $server->tmp());
    }
}
