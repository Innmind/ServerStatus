<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Cpu\Cores,
};
use Innmind\Immutable\{
    Str,
    Maybe,
};
use Symfony\Component\Process\Process;

final class OSXFacade
{
    /**
     * @return Maybe<Cpu>
     */
    public function __invoke(): Maybe
    {
        $process = Process::fromShellCommandline('top -l 1 -s 0 | grep \'CPU usage\'');
        $process->run();

        if (!$process->isSuccessful()) {
            /** @var Maybe<Cpu> */
            return Maybe::nothing();
        }

        $percentages = Str::of($process->getOutput())
            ->trim()
            ->capture(
                '~^CPU usage: (?P<user>\d+\.?\d*)% user, (?P<sys>\d+\.?\d*)% sys, (?P<idle>\d+\.?\d*)% idle$~'
            )
            ->map(static fn($_, $percentage) => $percentage->toString())
            ->map(static fn($_, $percentage) => (float) $percentage);

        $process = Process::fromShellCommandline('sysctl -a | grep \'hw.ncpu:\'');
        $process->run();

        $cores = Str::of($process->getOutput())
            ->trim()
            ->capture(
                '~^hw.ncpu: (?P<cores>\d+)$~'
            )
            ->get('cores')
            ->map(static fn($cores) => $cores->toString())
            ->map(static fn($cores) => (int) $cores)
            ->otherwise(static fn() => Maybe::just(1));
        $user = $percentages->get('user');
        $sys = $percentages->get('sys');
        $idle = $percentages->get('idle');

        return Maybe::all($user, $sys, $idle, $cores)
            ->map(static fn(float $user, float $sys, float $idle, int $cores) => new Cpu(
                new Percentage($user),
                new Percentage($sys),
                new Percentage($idle),
                new Cores($cores),
            ));
    }
}
