<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Servers\Linux,
    Server,
    Server\Cpu,
    Server\Memory,
    Server\LoadAverage,
    Server\Processes,
    Server\Disk
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use PHPUnit\Framework\TestCase;

class LinuxTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, new Linux(new Earth));
    }

    public function testCpu()
    {
        if (PHP_OS !== 'Linux') {
            return;
        }

        $this->assertInstanceOf(Cpu::class, (new Linux(new Earth))->cpu());
    }

    public function testMemory()
    {
        if (PHP_OS !== 'Linux') {
            return;
        }

        $this->assertInstanceOf(Memory::class, (new Linux(new Earth))->memory());
    }

    public function testProcesses()
    {
        $this->assertInstanceOf(Processes::class, (new Linux(new Earth))->processes());
    }

    public function testLoadAverage()
    {
        $this->assertInstanceOf(LoadAverage::class, (new Linux(new Earth))->loadAverage());
    }

    public function testDisk()
    {
        $this->assertInstanceOf(Disk::class, (new Linux(new Earth))->disk());
    }
}
