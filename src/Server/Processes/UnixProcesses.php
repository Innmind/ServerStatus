<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    Server\Process\User,
    Server\Process\Command,
    Server\Process\Memory,
    Server\Cpu\Percentage,
};
use Innmind\Server\Control\Server as Control;
use Innmind\TimeContinuum\{
    Clock,
    Format,
};
use Innmind\Validation\Is;
use Innmind\Immutable\{
    Str,
    Sequence,
    Set,
    Maybe,
    Monoid\Concat,
};

final class UnixProcesses implements Processes
{
    private function __construct(
        private Clock $clock,
        private Control\Processes $processes,
    ) {
    }

    /**
     * @internal
     */
    public static function of(Clock $clock, Control\Processes $processes): self
    {
        return new self($clock, $processes);
    }

    #[\Override]
    public function all(): Set
    {
        return $this
            ->run(
                Control\Command::foreground('ps')
                    ->withShortOption('eo', $this->format()),
            )
            ->map($this->parse(...))
            ->match(
                static fn($processes) => $processes,
                static fn() => Set::of(),
            );
    }

    #[\Override]
    public function get(Pid $pid): Maybe
    {
        return $this
            ->run(
                Control\Command::foreground('ps')
                    ->withShortOption('o', $this->format())
                    ->withShortOption('p', $pid->toString()),
            )
            ->otherwise(fn() => $this->run(
                Control\Command::foreground('ps')
                    ->withShortOption('o', $this->format())
                    ->withShortOption('q', $pid->toString()),
            ))
            ->map($this->parse(...))
            ->flatMap(static fn($processes) => $processes->find(
                static fn($process) => $process->pid()->equals($pid),
            ));
    }

    /**
     * @return Maybe<Str>
     */
    private function run(Control\Command $command): Maybe
    {
        return $this
            ->processes
            ->execute($command->withEnvironment('TZ', 'UTC'))
            ->maybe()
            ->flatMap(static fn($process) => $process->wait()->maybe())
            ->map(
                static fn($success) => $success
                    ->output()
                    ->map(static fn($chunk) => $chunk->data())
                    ->fold(new Concat),
            );
    }

    /**
     * @return Set<Process>
     */
    private function parse(Str $output): Set
    {
        $lines = $output
            ->trim()
            ->split("\n");

        $partsByLine = $lines
            ->drop(1) // columns name
            ->map(static fn($line) => $line->pregSplit('~ +~', 10)); // 6 columns + 4 spaces in the START column
        $processes = $partsByLine->map(function(Sequence $parts): Maybe {
            $startParts = $parts
                ->take(5)
                ->map(static fn(Str $part): string => $part->toString());
            /** @var non-empty-string */
            $start = Str::of(' ')->join($startParts)->toString();
            $parts = $parts
                ->drop(5)
                ->map(static fn($part) => $part->toString());
            $user = $parts
                ->get(0)
                ->keep(Is::string()->nonEmpty()->asPredicate())
                ->map(User::of(...));
            $pid = $parts
                ->get(1)
                ->map(static fn($value) => (int) $value)
                ->keep(Is::int()->positive()->asPredicate())
                ->map(Pid::of(...));
            $percentage = $parts
                ->get(2)
                ->map(static fn($value) => (float) $value)
                ->flatMap(Percentage::maybe(...));
            $memory = $parts
                ->get(3)
                ->map(static fn($value) => (float) $value)
                ->flatMap(Memory::maybe(...));
            $command = $parts
                ->get(4)
                ->keep(Is::string()->nonEmpty()->asPredicate())
                ->map(Command::of(...));

            return Maybe::all($user, $pid, $percentage, $memory, $command)
                ->map(fn(User $user, Pid $pid, Percentage $percentage, Memory $memory, Command $command) => Process::of(
                    $pid,
                    $user,
                    $percentage,
                    $memory,
                    $this->clock->at($start, Format::of('D M j H:i:s Y')),
                    $command,
                ));
        });

        /** @var Set<Process> */
        return $processes->reduce(
            Set::of(),
            static fn(Set $processes, Maybe $process): Set => $process->match(
                static fn(Process $process) => ($processes)($process),
                static fn() => $processes,
            ),
        );
    }

    private function format(): string
    {
        return \PHP_OS === 'Linux' ? 'lstart,user,pid,%cpu,%mem,cmd' : 'lstart,user,pid,%cpu,%mem,command';
    }
}
