<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server,
    Server\Processes,
    Server\LoadAverage,
    Server\Disk,
};
use Innmind\Url\Path;
use Innmind\Immutable\Attempt;
use Innmind\Immutable\Maybe;
use Psr\Log\LoggerInterface;

final class Logger implements Server
{
    private Server $server;
    private LoggerInterface $logger;

    public function __construct(Server $server, LoggerInterface $logger)
    {
        $this->server = $server;
        $this->logger = $logger;
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
    public function memory(): Maybe
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
        return new Processes\LoggerProcesses(
            $this->server->processes(),
            $this->logger,
        );
    }

    #[\Override]
    public function loadAverage(): LoadAverage
    {
        $loadAverage = $this->server->loadAverage();
        $this->logger->debug('Load average: {one} {five} {fifteen}', [
            'one' => $loadAverage->lastMinute(),
            'five' => $loadAverage->lastFiveMinutes(),
            'fifteen' => $loadAverage->lastFifteenMinutes(),
        ]);

        return $loadAverage;
    }

    #[\Override]
    public function disk(): Disk
    {
        return new Disk\LoggerDisk(
            $this->server->disk(),
            $this->logger,
        );
    }

    #[\Override]
    public function tmp(): Path
    {
        $tmp = $this->server->tmp();
        $this->logger->debug('Temporary folder located at: {path}', [
            'path' => $tmp->toString(),
        ]);

        return $tmp;
    }
}
