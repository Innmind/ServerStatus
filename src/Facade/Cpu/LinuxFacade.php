<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Cpu\Cores,
    Exception\CpuUsageNotAccessible,
};
use Innmind\Immutable\{
    Str,
    Maybe,
};
use Symfony\Component\Process\Process;

final class LinuxFacade
{
    public function __invoke(): Cpu
    {
        $process = Process::fromShellCommandline('top -bn1 | grep \'%Cpu\'');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new CpuUsageNotAccessible;
        }

        $percentages = Str::of($process->getOutput())
            ->trim()
            ->capture(
                '~^%Cpu\(s\): *(?P<user>\d+\.?\d*) us, *(?P<sys>\d+\.?\d*) sy, *(\d+\.?\d*) ni, *(?P<idle>\d+\.?\d*) id~'
            )
            ->map(static fn($_, $percentage) => $percentage->toString())
            ->map(static fn($_, $percentage) => (float) $percentage);

        $process = Process::fromShellCommandline('nproc');
        $process->run();
        $cores = 1;

        if ($process->isSuccessful()) {
            /** @psalm-suppress RedundantCastGivenDocblockType */
            $cores = ((int) (string) $process->getOutput()) ?: 1;
        }

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
                static fn($cpu) => $cpu,
                static fn() => throw new CpuUsageNotAccessible,
            );
    }
}
