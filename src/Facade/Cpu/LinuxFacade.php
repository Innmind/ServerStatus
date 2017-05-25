<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Facade\CpuFacade,
    Server\Cpu,
    Server\Cpu\Percentage,
    Exception\CpuUsageNotAccessible
};
use Innmind\Immutable\Str;
use Symfony\Component\Process\Process;

final class LinuxFacade implements CpuFacade
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
                '~^%Cpu(s):  (?P<user>\d+\.?\d*) us,  (?P<sys>\d+\.?\d*) sy,  (\d+\.?\d*) ni, (?P<idle>\d+\.?\d*) id~'
            );

        return new Cpu(
            new Percentage((float) (string) $percentages->get('user')),
            new Percentage((float) (string) $percentages->get('sys')),
            new Percentage((float) (string) $percentages->get('idle'))
        );
    }
}
