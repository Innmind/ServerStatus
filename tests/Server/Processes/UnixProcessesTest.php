<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes\UnixProcesses,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    Clock\PointInTime\Delay,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\{
    Clock as ClockInterface,
    Earth\Clock,
};
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class UnixProcessesTest extends TestCase
{
    private $processes;

    public function setUp(): void
    {
        $this->processes = new UnixProcesses(
            new Clock,
            Control::build(
                new Clock,
                Streams::fromAmbientAuthority(),
                new Usleep,
            )->processes(),
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, $this->processes);
    }

    public function testAll()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $all = $this->processes->all();

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

        $process = $this
            ->processes
            ->get(new Pid(1))
            ->match(
                static fn($process) => $process,
                static fn() => null,
            );

        $this->assertInstanceOf(Process::class, $process);
        $this->assertSame('root', $process->user()->toString());
    }

    public function testReturnNothingWhenProcessDoesntExist()
    {
        $this->assertNull(
            $this
                ->processes
                ->get(new Pid(42424))
                ->match(
                    static fn($process) => $process,
                    static fn() => null,
                ),
        );
    }

    public function testProcessTimeIsStillAccessible()
    {
        if (!\in_array(\PHP_OS, ['Darwin', 'Linux'], true)) {
            $this->markTestSkipped();
        }

        $process = $this
            ->processes
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
