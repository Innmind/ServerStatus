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
use Innmind\Validation\Is;
use Innmind\Immutable\{
    Str,
    Attempt,
    Maybe,
    Monoid\Concat,
};

/**
 * @internal
 */
final class OSXFacade
{
    public function __construct(
        private Processes $processes,
    ) {
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
                    ->withShortOption('l', '1')
                    ->withShortOption('s', '0')
                    ->pipe(
                        Command::foreground('grep')
                            ->withArgument('CPU usage'),
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
                    ->fold(Concat::monoid),
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
                '~^CPU usage: (?P<user>\d+\.?\d*)% user, (?P<sys>\d+\.?\d*)% sys, (?P<idle>\d+\.?\d*)% idle$~',
            )
            ->map(static fn($_, $percentage) => $percentage->toString())
            ->map(static fn($_, $percentage) => (float) $percentage);

        $cores = $this
            ->processes
            ->execute(
                Command::foreground('sysctl')
                    ->withShortOption('a')
                    ->pipe(
                        Command::foreground('grep')
                            ->withArgument('hw.ncpu:'),
                    ),
            )
            ->maybe()
            ->flatMap(static fn($process) => $process->wait()->maybe())
            ->map(
                static fn($success) => $success
                    ->output()
                    ->map(static fn($chunk) => $chunk->data())
                    ->fold(Concat::monoid),
            )
            ->map(static fn($output) => $output->trim())
            ->map(static fn($output) => $output->capture('~^hw.ncpu: (?P<cores>\d+)$~'))
            ->flatMap(static fn($output) => $output->get('cores'))
            ->map(static fn($cores) => $cores->toString())
            ->map(static fn($cores) => (int) $cores)
            ->keep(Is::int()->positive()->asPredicate())
            ->otherwise(static fn() => Maybe::just(1))
            ->map(Cores::of(...));
        $user = $percentages
            ->get('user')
            ->flatMap(Percentage::maybe(...));
        $sys = $percentages
            ->get('sys')
            ->flatMap(Percentage::maybe(...));
        $idle = $percentages
            ->get('idle')
            ->flatMap(Percentage::maybe(...));

        return Maybe::all($user, $sys, $idle, $cores)
            ->map(Cpu::of(...))
            ->match(
                Attempt::result(...),
                static fn() => Attempt::error(new \RuntimeException('Failed to parse CPU usage')),
            );
    }
}
