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

        $percentages = (new Str($process->getOutput()))
            ->trim()
            ->capture(
                '~^CPU usage: (?P<user>\d+\.?\d*)% user, (?P<sys>\d+\.?\d*)% sys, (?P<idle>\d+\.?\d*)% idle$~'
            );

        $process = Process::fromShellCommandline('sysctl -a | grep \'hw.ncpu:\'');
        $process->run();

        if ($process->isSuccessful()) {
            $match = (new Str($process->getOutput()))
                ->trim()
                ->capture(
                    '~^hw.ncpu: (?P<cores>\d+)$~'
                );
            $cores = $match['cores'];
        }

        return new Cpu(
            new Percentage((float) (string) $percentages->get('user')),
            new Percentage((float) (string) $percentages->get('sys')),
            new Percentage((float) (string) $percentages->get('idle')),
            new Cores((int) (string) ($cores ?? 1)),
        );
    }
}
