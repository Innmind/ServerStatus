<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\LoadAverage;

use Innmind\Server\Status\{
    Facade\LoadAverageFacade,
    Server\LoadAverage
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    ElapsedPeriod
};

final class CacheDecorator implements LoadAverageFacade
{
    private $facade;
    private $threshold;
    private $loadedAt;
    private $loadAverage;
    private $clock;

    public function __construct(
        LoadAverageFacade $facade,
        TimeContinuumInterface $clock,
        int $threshold
    ) {
        $this->facade = $facade;
        $this->clock = $clock;
        $this->threshold = new ElapsedPeriod($threshold);
    }

    public function __invoke(): LoadAverage
    {
        if (!$this->loadedAt) {
            return $this->load();
        }

        $elapsed = $this
            ->clock
            ->now()
            ->elapsedSince($this->loadedAt);

        if ($elapsed->longerThan($this->threshold)) {
            return $this->load();
        }

        return $this->loadAverage;
    }

    private function load(): LoadAverage
    {
        $this->loadedAt = $this->clock->now();

        return $this->loadAverage = ($this->facade)();
    }
}
