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
            );

        $process = Process::fromShellCommandline('nproc');
        $process->run();

        if ($process->isSuccessful()) {
            /** @psalm-suppress RedundantCastGivenDocblockType */
            $cores = ((int) (string) $process->getOutput()) ?: 1;
        }

        return new Cpu(
            new Percentage((float) $percentages->get('user')->toString()),
            new Percentage((float) $percentages->get('sys')->toString()),
            new Percentage((float) $percentages->get('idle')->toString()),
            new Cores((int) (string) ($cores ?? 1)),
        );
    }
}
