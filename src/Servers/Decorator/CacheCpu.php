<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers\Decorator;

use Innmind\Server\Status\{
    Server,
    Server\Cpu,
    Server\Memory,
    Server\Processes,
    Server\LoadAverage,
    Server\Disk,
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod,
    PointInTimeInterface,
};
use Innmind\Url\PathInterface;

final class CacheCpu implements Server
{
    private Server $server;
    private TimeContinuumInterface $clock;
    private ElapsedPeriod $threshold;
    private ?PointInTimeInterface $cachedAt = null;
    private ?Cpu $data = null;

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
        $now = $this->clock->now();

        if (
            $this->cachedAt &&
            $this->threshold->longerThan(
                $now->elapsedSince($this->cachedAt)
            )
        ) {
            return $this->data;
        }

        $this->data = $this->server->cpu();
        $this->cachedAt = $now;

        return $this->data;
    }

    public function memory(): Memory
    {
        return $this->server->memory();
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
