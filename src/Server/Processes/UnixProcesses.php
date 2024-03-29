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
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\{
    Str,
    Sequence,
    Set,
    Maybe,
};

final class UnixProcesses implements Processes
{
    private Clock $clock;
    private Control\Processes $processes;

    public function __construct(Clock $clock, Control\Processes $processes)
    {
        $this->clock = $clock;
        $this->processes = $processes;
    }

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
            ->wait()
            ->maybe()
            ->map(static fn($success) => $success->output()->toString())
            ->map(Str::of(...));
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
            $start = Str::of(' ')->join($startParts)->toString();
            $parts = $parts
                ->drop(5)
                ->map(static fn($part) => $part->toString());
            $user = $parts->get(0);
            $pid = $parts->get(1);
            $percentage = $parts->get(2);
            $memory = $parts->get(3);
            $command = $parts->get(4);

            return Maybe::all($user, $pid, $percentage, $memory, $command)
                ->map(fn(string $user, string $pid, string $percentage, string $memory, string $command) => new Process(
                    new Pid((int) $pid),
                    new User($user),
                    new Percentage((float) $percentage),
                    new Memory((float) $memory),
                    $this->clock->at($start),
                    new Command($command),
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
