<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server,
    Server\Cpu,
    Server\Memory,
    Server\Processes,
    Server\LoadAverage,
    Server\Disk,
};
use Innmind\Url\Path;
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

    public function cpu(): Cpu
    {
        $cpu = $this->server->cpu();
        $this->logger->debug($cpu->toString());

        return $cpu;
    }

    public function memory(): Memory
    {
        $memory = $this->server->memory();
        $this->logger->debug('Memory usage: {total} {wired} {active} {free} {swap} {used}', [
            'total' => $memory->total()->toString(),
            'wired' => $memory->wired()->toString(),
            'active' => $memory->active()->toString(),
            'free' => $memory->free()->toString(),
            'swap' => $memory->swap()->toString(),
            'used' => $memory->used()->toString(),
        ]);

        return $memory;
    }

    public function processes(): Processes
    {
        return new Processes\LoggerProcesses(
            $this->server->processes(),
            $this->logger,
        );
    }

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

    public function disk(): Disk
    {
        return new Disk\LoggerDisk(
            $this->server->disk(),
            $this->logger,
        );
    }

    public function tmp(): Path
    {
        $tmp = $this->server->tmp();
        $this->logger->debug('Temporary folder located at: {path}', [
            'path' => $tmp->toString(),
        ]);

        return $tmp;
    }
}
