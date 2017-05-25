<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes\UnixProcesses,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    Exception\InformationNotAccessible
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use Innmind\Immutable\MapInterface;
use PHPUnit\Framework\TestCase;

class UnixProcessesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, new UnixProcesses(new Earth));
    }

    public function testAll()
    {
        if (!in_array(PHP_OS, ['Darwin', 'Linux'])) {
            return;
        }

        $all = (new UnixProcesses(new Earth))->all();

        $this->assertInstanceOf(MapInterface::class, $all);
        $this->assertSame('int', (string) $all->keyType());
        $this->assertSame(Process::class, (string) $all->valueType());
        $this->assertTrue($all->size() > 0);
        $this->assertSame('root', (string) $all->get(1)->user());
    }

    public function testGet()
    {
        if (!in_array(PHP_OS, ['Darwin', 'Linux'])) {
            return;
        }

        $process = (new UnixProcesses(new Earth))->get(new Pid(1));

        $this->assertInstanceOf(Process::class, $process);
        $this->assertSame('root', (string) $process->user());
    }

    public function testThrowWhenProcessFails()
    {
        $this->expectException(InformationNotAccessible::class);

        (new UnixProcesses(new Earth))->get(new Pid(42424));
    }
}
