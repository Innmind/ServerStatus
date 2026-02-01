<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
};
use Innmind\Time\Format;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class Logger implements Implementation
{
    private function __construct(
        private Implementation $processes,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @internal
     */
    public static function of(Implementation $processes, LoggerInterface $logger): self
    {
        return new self($processes, $logger);
    }

    #[\Override]
    public function all(): Sequence
    {
        $all = $this->processes->all();
        $this->logger->debug('{count} processes currently running', [
            'count' => $all->size(),
            'processes' => $all->reduce(
                [],
                function(array $processes, Process $process): array {
                    $processes[] = $this->normalize($process);

                    return $processes;
                },
            ),
        ]);

        return $all;
    }

    #[\Override]
    public function get(Pid $pid): Maybe
    {
        return $this
            ->processes
            ->get($pid)
            ->map(function($process) {
                $this->logger->debug(
                    'Process {pid} currently running',
                    $this->normalize($process),
                );

                return $process;
            });
    }

    private function normalize(Process $process): array
    {
        return [
            'pid' => $process->pid()->toInt(),
            'user' => $process->user()->toString(),
            'cpu' => $process->cpu()->toString(),
            'memory' => $process->memory()->toString(),
            'start' => $process
                ->start()
                ->map(static fn($start) => $start->format(Format::iso8601()))
                ->match(
                    static fn($start) => $start,
                    static fn() => null,
                ),
            'command' => $process->command()->toString(),
        ];
    }
}
