<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
};
use Innmind\TimeContinuum\Earth\Format\ISO8601;
use Innmind\Immutable\Map;
use Psr\Log\LoggerInterface;

final class LoggerProcesses implements Processes
{
    private Processes $processes;
    private LoggerInterface $logger;

    public function __construct(Processes $processes, LoggerInterface $logger)
    {
        $this->processes = $processes;
        $this->logger = $logger;
    }

    public function all(): Map
    {
        $all = $this->processes->all();
        $this->logger->debug('{count} processes currently running', [
            'count' => $all->size(),
            'processes' => $all->reduce(
                [],
                function(array $processes, int $_, Process $process): array {
                    $processes[] = $this->normalize($process);

                    return $processes;
                },
            ),
        ]);

        return $all;
    }

    public function get(Pid $pid): Process
    {
        $process = $this->processes->get($pid);
        $this->logger->debug('Process {pid} currently running', $this->normalize($process));

        return $process;
    }

    private function normalize(Process $process): array
    {
        return [
            'pid' => $process->pid()->toInt(),
            'user' => $process->user()->toString(),
            'cpu' => $process->cpu()->toString(),
            'memory' => $process->memory()->toString(),
            'start' => $process->start()->format(new ISO8601),
            'command' => $process->command()->toString(),
        ];
    }
}
