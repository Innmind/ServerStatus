<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers\Decorator;

use Innmind\Server\Status\{
    Server,
    Server\Cpu,
    Server\Memory,
    Server\Processes,
    Server\LoadAverage,
    Server\Disk
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod
};
use Innmind\Url\PathInterface;

final class CacheMemory implements Server
{
    private $server;
    private $clock;
    private $threshold;
    private $cachedAt;
    private $data;

    public function __construct(
        Server $server,
        TimeContinuumInterface $clock,
        ElapsedPeriod $threshold
    ) {
        $this->server = $server;
        $this->clock = $clock;
        $this->threshold = $threshold;
    }

    public function cpu(): Cpu
    {
        return $this->server->cpu();
    }

    public function memory(): Memory
    {
        $now = $this->clock->now();

        if (
            $this->cachedAt &&
            $this->threshold->longerThan(
                $now->elapsedSince($this->cachedAt)
            )
        ) {
            return $this->data;
        }

        $this->data = $this->server->memory();
        $this->cachedAt = $now;

        return $this->data;
    }

    public function processes(): Processes
    {
        return $this->server->processes();
    }

    public function loadAverage(): LoadAverage
    {
        return $this->server->loadAverage();
    }

    public function disk(): Disk
    {
        return $this->server->disk();
    }

    public function tmp(): PathInterface
    {
        return $this->server->tmp();
    }
}
