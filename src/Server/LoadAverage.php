<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Exception\LoadAverageCannotBeNegative;

final class LoadAverage
{
    private float $lastMinute;
    private float $lastFiveMinutes;
    private float $lastFifteenMinutes;

    public function __construct(
        float $lastMinute,
        float $lastFiveMinutes,
        float $lastFifteenMinutes
    ) {
        if ($lastMinute < 0 || $lastFiveMinutes < 0 || $lastFifteenMinutes < 0) {
            throw new LoadAverageCannotBeNegative(
                (string) \min($lastMinute, $lastFiveMinutes, $lastFifteenMinutes),
            );
        }

        $this->lastMinute = $lastMinute;
        $this->lastFiveMinutes = $lastFiveMinutes;
        $this->lastFifteenMinutes = $lastFifteenMinutes;
    }

    public function lastMinute(): float
    {
        return $this->lastMinute;
    }

    public function lastFiveMinutes(): float
    {
        return $this->lastFiveMinutes;
    }

    public function lastFifteenMinutes(): float
    {
        return $this->lastFifteenMinutes;
    }
}
