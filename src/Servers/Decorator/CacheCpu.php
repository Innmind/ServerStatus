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
    Clock,
    ElapsedPeriod,
    PointInTime,
};
use Innmind\Url\Path;

final class CacheCpu implements Server
{
    private Server $server;
    private Clock $clock;
    private ElapsedPeriod $threshold;
    private ?PointInTime $cachedAt = null;
    private ?Cpu $data = null;

    public function __construct(
        Server $server,
        Clock $clock,
        ElapsedPeriod $threshold
    ) {
        $this->server = $server;
        $this->clock = $clock;
        $this->threshold = $threshold;
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     */
    public function cpu(): Cpu
    {
        $now = $this->clock->now();

        if (
            $this->cachedAt &&
            $this->threshold->longerThan(
                $now->elapsedSince($this->cachedAt)
            )
        ) {
            /** @psalm-suppress NullableReturnStatement */
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

    public function tmp(): Path
    {
        return $this->server->tmp();
    }
}
