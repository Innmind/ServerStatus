<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes\UnixProcesses,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    Clock\PointInTime\Delay,
    Exception\InformationNotAccessible,
};
use Innmind\TimeContinuum\{
    Clock as ClockInterface,
    Earth\Clock,
};
use Innmind\Immutable\Set;
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

        $this->assertInstanceOf(Set::class, $all);
        $this->assertNotEmpty($all);
        $this->assertSame(
            'root',
            $all
                ->find(static fn($process) => $process->pid()->is(1))
                ->map(static fn($process) => $process->user())
                ->map(static fn($user) => $user->toString())
                ->match(
                    static fn($user) => $user,
                    static fn() => null,
                ),
        );
    }

    public function testGet()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $process = (new UnixProcesses(new Clock))
            ->get(new Pid(1))
            ->match(
                static fn($process) => $process,
                static fn() => null,
            );

        $this->assertInstanceOf(Process::class, $process);
        $this->assertSame('root', $process->user()->toString());
    }

    public function testThrowWhenProcessFails()
    {
        $this->expectException(InformationNotAccessible::class);

        (new UnixProcesses(new Clock))->get(new Pid(42424));
    }

    public function testProcessTimeIsStillAccessible()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $process = (new UnixProcesses(new Clock))
            ->all()
            ->find(static fn($process) => $process->pid()->is(1))
            ->match(
                static fn($process) => $process,
                static fn() => null,
            );

        $this->assertInstanceOf(Process::class, $process);
        $this->assertIsInt($process->start()->match(
            static fn($start) => $start->milliseconds(),
            static fn() => null,
        ));
    }
}
