<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Server\Cpu,
    Server\Cpu\Percentage,
    Server\Cpu\Cores,
    Exception\CpuUsageNotAccessible
};
use Innmind\Immutable\Str;
use Symfony\Component\Process\Process;

final class LinuxFacade
{
    public function __invoke(): Cpu
    {
        $process = new Process('top -bn1 | grep \'%Cpu\'');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new CpuUsageNotAccessible;
        }

        $percentages = (new Str($process->getOutput()))
            ->trim()
            ->capture(
                '~^%Cpu\(s\): *(?P<user>\d+\.?\d*) us, *(?P<sys>\d+\.?\d*) sy, *(\d+\.?\d*) ni, *(?P<idle>\d+\.?\d*) id~'
            );

        $process = new Process('nproc');
        $process->run();

        if ($process->isSuccessful()) {
            $cores = ((int) (string) $process->getOutput()) ?: 1;
        }

        return new Cpu(
            new Percentage((float) (string) $percentages->get('user')),
            new Percentage((float) (string) $percentages->get('sys')),
            new Percentage((float) (string) $percentages->get('idle')),
            new Cores((int) (string) ($cores ?? 1))
        );
    }
}
