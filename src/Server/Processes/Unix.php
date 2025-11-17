<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
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
    Maybe,
    Monoid\Concat,
};

/**
 * @internal
 */
final class Unix implements Implementation
{
    private function __construct(
        private Clock $clock,
        private Control\Processes $processes,
        private string $format,
    ) {
    }

    /**
     * @internal
     */
    public static function osx(Clock $clock, Control\Processes $processes): self
    {
        return new self(
            $clock,
            $processes,
            'lstart,user,pid,%cpu,%mem,command',
        );
    }

    /**
     * @internal
     */
    public static function linux(Clock $clock, Control\Processes $processes): self
    {
        return new self(
            $clock,
            $processes,
            'lstart,user,pid,%cpu,%mem,cmd',
        );
    }

    #[\Override]
    public function all(): Sequence
    {
        return $this
            ->run(
                Control\Command::foreground('ps')
                    ->withShortOption('eo', $this->format),
            )
            ->map($this->parse(...))
            ->toSequence()
            ->flatMap(static fn($processes) => $processes);
    }

    #[\Override]
    public function get(Pid $pid): Maybe
    {
        return $this
            ->run(
                Control\Command::foreground('ps')
                    ->withShortOption('o', $this->format)
                    ->withShortOption('p', $pid->toString()),
            )
            ->otherwise(fn() => $this->run(
                Control\Command::foreground('ps')
                    ->withShortOption('o', $this->format)
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
     * @return Sequence<Process>
     */
    private function parse(Str $output): Sequence
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
            $start = Maybe::just(
                $this->clock->at($start, Format::of('D M j H:i:s Y'))->maybe(),
            );

            return Maybe::all($pid, $user, $percentage, $memory, $start, $command)
                ->map(Process::of(...));
        });

        return $processes->flatMap(
            static fn($process) => $process->toSequence(), // discard process that failed to be parsed
        );
    }
}
