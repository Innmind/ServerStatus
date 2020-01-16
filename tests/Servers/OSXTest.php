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
    Server\Disk
};
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;

class OSXTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, new OSX(new Clock));
    }

    public function testCpu()
    {
        if (PHP_OS !== 'Darwin') {
            return;
        }

        $this->assertInstanceOf(Cpu::class, (new OSX(new Clock))->cpu());
    }

    public function testMemory()
    {
        if (PHP_OS !== 'Darwin') {
            return;
        }

        $this->assertInstanceOf(Memory::class, (new OSX(new Clock))->memory());
    }

    public function testProcesses()
    {
        $this->assertInstanceOf(Processes::class, (new OSX(new Clock))->processes());
    }

    public function testLoadAverage()
    {
        $this->assertInstanceOf(LoadAverage::class, (new OSX(new Clock))->loadAverage());
    }

    public function testDisk()
    {
        $this->assertInstanceOf(Disk::class, (new OSX(new Clock))->disk());
    }

    public function testTmp()
    {
        $server = new OSX(new Clock);

        $this->assertInstanceOf(Path::class, $server->tmp());
        $this->assertSame(\sys_get_temp_dir(), $server->tmp()->toString());
    }
}
