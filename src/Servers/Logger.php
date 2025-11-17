<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server\Processes,
    Server\Disk,
};
use Innmind\Immutable\Attempt;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class Logger implements Implementation
{
    private function __construct(
        private Implementation $server,
        private LoggerInterface $logger,
    ) {
    }

    public static function of(Implementation $server, LoggerInterface $logger): self
    {
        return new self($server, $logger);
    }

    #[\Override]
    public function cpu(): Attempt
    {
        return $this
            ->server
            ->cpu()
            ->map(function($cpu) {
                $this->logger->debug($cpu->toString());

                return $cpu;
            });
    }

    #[\Override]
    public function memory(): Attempt
    {
        return $this
            ->server
            ->memory()
            ->map(function($memory) {
                $this->logger->debug('Memory usage: {total} {active} {free} {swap} {used}', [
                    'total' => $memory->total()->toString(),
                    'active' => $memory->active()->toString(),
                    'free' => $memory->free()->toString(),
                    'swap' => $memory->swap()->toString(),
                    'used' => $memory->used()->toString(),
                ]);

                return $memory;
            });
    }

    #[\Override]
    public function processes(): Processes
    {
        return Processes\Logger::of(
            $this->server->processes(),
            $this->logger,
        );
    }

    #[\Override]
    public function loadAverage(): Attempt
    {
        return $this->server->loadAverage()->map(function($loadAverage) {
            $this->logger->debug('Load average: {one} {five} {fifteen}', [
                'one' => $loadAverage->lastMinute(),
                'five' => $loadAverage->lastFiveMinutes(),
                'fifteen' => $loadAverage->lastFifteenMinutes(),
            ]);

            return $loadAverage;
        });
    }

    #[\Override]
    public function disk(): Disk
    {
        return Disk\Logger::of(
            $this->server->disk(),
            $this->logger,
        );
    }
}
