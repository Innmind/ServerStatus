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
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;

class LinuxTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Server::class, new Linux(new Clock));
    }

    public function testCpu()
    {
        if (\PHP_OS !== 'Linux') {
            $this->markTestSkipped();
        }

        $this->assertInstanceOf(
            Cpu::class,
            (new Linux(new Clock))
                ->cpu()
                ->match(
                    static fn($cpu) => $cpu,
                    static fn() => null,
                ),
        );
    }

    public function testMemory()
    {
        if (\PHP_OS !== 'Linux') {
            $this->markTestSkipped();
        }

        $this->assertInstanceOf(
            Memory::class,
            (new Linux(new Clock))
                ->memory()
                ->match(
                    static fn($memory) => $memory,
                    static fn() => null,
                ),
        );
    }

    public function testProcesses()
    {
        $this->assertInstanceOf(Processes::class, (new Linux(new Clock))->processes());
    }

    public function testLoadAverage()
    {
        $this->assertInstanceOf(LoadAverage::class, (new Linux(new Clock))->loadAverage());
    }

    public function testDisk()
    {
        $this->assertInstanceOf(Disk::class, (new Linux(new Clock))->disk());
    }

    public function testTmp()
    {
        $server = new Linux(new Clock);

        $this->assertInstanceOf(Path::class, $server->tmp());
        $this->assertSame(\sys_get_temp_dir(), $server->tmp()->toString());
    }
}
