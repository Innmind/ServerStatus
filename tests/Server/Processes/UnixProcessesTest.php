<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes\UnixProcesses,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    Exception\InformationNotAccessible,
};
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class UnixProcessesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, new UnixProcesses(new Clock));
    }

    public function testAll()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $all = (new UnixProcesses(new Clock))->all();

        $this->assertInstanceOf(Map::class, $all);
        $this->assertSame('int', (string) $all->keyType());
        $this->assertSame(Process::class, (string) $all->valueType());
        $this->assertTrue($all->size() > 0);
        $this->assertSame('root', $all->get(1)->user()->toString());
    }

    public function testGet()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $process = (new UnixProcesses(new Clock))->get(new Pid(1));

        $this->assertInstanceOf(Process::class, $process);
        $this->assertSame('root', $process->user()->toString());
    }

    public function testThrowWhenProcessFails()
    {
        $this->expectException(InformationNotAccessible::class);

        (new UnixProcesses(new Clock))->get(new Pid(42424));
    }
}
