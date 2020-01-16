<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Cpu\Cores,
    Exception\CpuUsageNotAccessible,
};
use Innmind\Immutable\Str;
use Symfony\Component\Process\Process;

final class OSXFacade
{
    public function __invoke(): Cpu
    {
        $process = Process::fromShellCommandline('top -l 1 -s 0 | grep \'CPU usage\'');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new CpuUsageNotAccessible;
        }

        $percentages = Str::of($process->getOutput())
            ->trim()
            ->capture(
                '~^CPU usage: (?P<user>\d+\.?\d*)% user, (?P<sys>\d+\.?\d*)% sys, (?P<idle>\d+\.?\d*)% idle$~'
            );

        $process = Process::fromShellCommandline('sysctl -a | grep \'hw.ncpu:\'');
        $process->run();

        if ($process->isSuccessful()) {
            $match = Str::of($process->getOutput())
                ->trim()
                ->capture(
                    '~^hw.ncpu: (?P<cores>\d+)$~'
                );
            $cores = $match->get('cores')->toString();
        }

        return new Cpu(
            new Percentage((float) $percentages->get('user')->toString()),
            new Percentage((float) $percentages->get('sys')->toString()),
            new Percentage((float) $percentages->get('idle')->toString()),
            new Cores((int) ($cores ?? 1)),
        );
    }
}
