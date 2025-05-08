<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes\UnixProcesses,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\IO\IO;
use Innmind\Immutable\Set;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class UnixProcessesTest extends TestCase
{
    private $processes;

    public function setUp(): void
    {
        $this->processes = new UnixProcesses(
            Clock::live(),
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Usleep::new(),
            )->processes(),
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, $this->processes);
    }

    public function testAll()
    {
        $all = $this->processes->all();

        $this->assertInstanceOf(Set::class, $all);
        $this->assertGreaterThanOrEqual(1, $all->size());
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
        $this->assertNotContains(
            null,
            $all
                ->map(
                    static fn($process) => $process
                        ->start()
                        ->match(
                            static fn($point) => $point,
                            static fn() => null,
                        ),
                )
                ->toList(),
        );
    }

    public function testGet()
    {
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
        $process = $this
            ->processes
            ->all()
            ->find(static fn($process) => $process->pid()->is(1))
            ->match(
                static fn($process) => $process,
                static fn() => null,
            );

        $this->assertInstanceOf(Process::class, $process);
        $this->assertInstanceOf(PointInTime::class, $process->start()->match(
            static fn($start) => $start,
            static fn() => null,
        ));
    }
}
