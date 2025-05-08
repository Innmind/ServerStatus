<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Cpu\Cores,
};
use Innmind\Server\Control\Server\{
    Processes,
    Command,
};
use Innmind\Immutable\{
    Str,
    Attempt,
    Maybe,
    Monoid\Concat,
};

/**
 * @internal
 */
final class LinuxFacade
{
    private Processes $processes;

    public function __construct(Processes $processes)
    {
        $this->processes = $processes;
    }

    /**
     * @return Attempt<Cpu>
     */
    public function __invoke(): Attempt
    {
        return $this
            ->processes
            ->execute(
                Command::foreground('top')
                    ->withShortOption('bn1')
                    ->pipe(
                        Command::foreground('grep')
                            ->withArgument('%Cpu'),
                    ),
            )
            ->flatMap(static fn($process) => $process->wait()->match(
                Attempt::result(...),
                static fn() => Attempt::error(new \RuntimeException('Failed to retrieve CPU usage')),
            ))
            ->map(
                static fn($success) => $success
                    ->output()
                    ->map(static fn($chunk) => $chunk->data())
                    ->fold(new Concat),
            )
            ->flatMap($this->parse(...));
    }

    /**
     * @return Attempt<Cpu>
     */
    private function parse(Str $output): Attempt
    {
        $percentages = $output
            ->trim()
            ->capture(
                '~^%Cpu\(s\): *(?P<user>\d+\.?\d*) us, *(?P<sys>\d+\.?\d*) sy, *(\d+\.?\d*) ni, *(?P<idle>\d+\.?\d*) id~',
            )
            ->map(static fn($_, $percentage) => $percentage->toString())
            ->map(static fn($_, $percentage) => (float) $percentage);

        $cores = $this
            ->processes
            ->execute(Command::foreground('nproc'))
            ->maybe()
            ->flatMap(static fn($process) => $process->wait()->maybe())
            ->map(
                static fn($success) => $success
                    ->output()
                    ->map(static fn($chunk) => $chunk->data())
                    ->fold(new Concat)
                    ->toString(),
            )
            ->map(static fn($cores) => (int) $cores)
            ->match(
                static fn($cores) => $cores,
                static fn() => 1,
            );

        $user = $percentages->get('user');
        $sys = $percentages->get('sys');
        $idle = $percentages->get('idle');

        return Maybe::all($user, $sys, $idle)
            ->map(static fn(float $user, float $sys, float $idle) => new Cpu(
                new Percentage($user),
                new Percentage($sys),
                new Percentage($idle),
                new Cores($cores),
            ))
            ->match(
                Attempt::result(...),
                static fn() => Attempt::error(new \RuntimeException('Failed to parse CPU usage')),
            );
    }
}
