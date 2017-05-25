<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Servers\OSX,
    Server,
    Server\Cpu,
    Server\Memory,
    Server\LoadAverage,
    Server\Processes
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use PHPUnit\Framework\TestCase;

class OSXTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, new OSX(new Earth));
    }

    public function testCpu()
    {
        if (PHP_OS !== 'Darwin') {
            return;
        }

        $this->assertInstanceOf(Cpu::class, (new OSX(new Earth))->cpu());
    }

    public function testMemory()
    {
        if (PHP_OS !== 'Darwin') {
            return;
        }

        $this->assertInstanceOf(Memory::class, (new OSX(new Earth))->memory());
    }

    public function testProcesses()
    {
        $this->assertInstanceOf(Processes::class, (new OSX(new Earth))->processes());
    }

    public function testLoadAverage()
    {
        $this->assertInstanceOf(LoadAverage::class, (new OSX(new Earth))->loadAverage());
    }
}
