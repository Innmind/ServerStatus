<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class LoadAverage
{
    private function __construct(
        private float $lastMinute,
        private float $lastFiveMinutes,
        private float $lastFifteenMinutes,
    ) {
    }

    /**
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    public static function maybe(
        float $lastMinute,
        float $lastFiveMinutes,
        float $lastFifteenMinutes,
    ): Maybe {
        if ($lastMinute < 0 || $lastFiveMinutes < 0 || $lastFifteenMinutes < 0) {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        return Maybe::just(new self($lastMinute, $lastFiveMinutes, $lastFifteenMinutes));
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
