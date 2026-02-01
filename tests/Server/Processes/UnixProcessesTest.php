<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    ServerFactory,
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    EnvironmentPath,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\Time\{
    Clock,
    Point,
    Halt,
};
use Innmind\IO\IO;
use Innmind\Immutable\Sequence;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class UnixProcessesTest extends TestCase
{
    private $processes;

    public function setUp(): void
    {
        $this->processes = ServerFactory::build(
            Clock::live(),
            Control::build(
                Clock::live(),
                IO::fromAmbientAuthority(),
                Halt::new(),
            ),
            EnvironmentPath::of(\getenv('PATH')),
        )->processes();
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Processes::class, $this->processes);
    }

    public function testAll()
    {
        $all = $this->processes->all();

        $this->assertInstanceOf(Sequence::class, $all);
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
            ->get(Pid::of(1))
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
                ->get(Pid::of(42424))
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
        $this->assertInstanceOf(Point::class, $process->start()->match(
            static fn($start) => $start,
            static fn() => null,
        ));
    }
}
