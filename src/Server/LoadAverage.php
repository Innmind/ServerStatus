<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Exception\LoadAverageCannotBeNegative;

final class LoadAverage
{
    private $lastMinute;
    private $lastFiveMinutes;
    private $lastFifteenMinutes;

    public function __construct(
        int $lastMinute,
        int $lastFiveMinutes,
        int $lastFifteenMinutes
    ) {
        if ($lastMinute < 0 || $lastFiveMinutes < 0 || $lastFifteenMinutes < 0) {
            throw new LoadAverageCannotBeNegative;
        }

        $this->lastMinute = $lastMinute;
        $this->lastFiveMinutes = $lastFiveMinutes;
        $this->lastFifteenMinutes = $lastFifteenMinutes;
    }

    public function lastMinute(): int
    {
        return $this->lastMinute;
    }

    public function lastFiveMinutes(): int
    {
        return $this->lastFiveMinutes;
    }

    public function lastFifteenMinutes(): int
    {
        return $this->lastFifteenMinutes;
    }
}
