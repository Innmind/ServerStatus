<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Clock\PointInTime;

use Innmind\TimeContinuum\{
    Clock,
    Clock\Year,
    Clock\Month,
    Clock\Day,
    Clock\Hour,
    Clock\Minute,
    Clock\Second,
    Clock\Millisecond,
    PointInTime,
    ElapsedPeriod,
    Format,
    Timezone,
    Period,
};

/**
 * @psalm-immutable
 */
final class Delay implements PointInTime
{
    private Clock $clock;
    private string $time;

    public function __construct(Clock $clock, string $time)
    {
        $this->clock = $clock;
        $this->time = $time;
    }

    public function milliseconds(): int
    {
        return $this->time()->milliseconds();
    }

    public function year(): Year
    {
        return $this->time()->year();
    }

    public function month(): Month
    {
        return $this->time()->month();
    }

    public function day(): Day
    {
        return $this->time()->day();
    }

    public function hour(): Hour
    {
        return $this->time()->hour();
    }

    public function minute(): Minute
    {
        return $this->time()->minute();
    }

    public function second(): Second
    {
        return $this->time()->second();
    }

    public function millisecond(): Millisecond
    {
        return $this->time()->millisecond();
    }

    public function format(Format $format): string
    {
        return $this->time()->format($format);
    }

    public function changeTimezone(Timezone $zone): PointInTime
    {
        return $this->time()->changeTimezone($zone);
    }

    public function timezone(): Timezone
    {
        return $this->time()->timezone();
    }

    public function elapsedSince(PointInTime $point): ElapsedPeriod
    {
        return $this->time()->elapsedSince($point);
    }

    public function goBack(Period $period): PointInTime
    {
        return $this->time()->goBack($period);
    }

    public function goForward(Period $period): PointInTime
    {
        return $this->time()->goForward($period);
    }

    public function equals(PointInTime $point): bool
    {
        return $this->time()->equals($point);
    }

    public function aheadOf(PointInTime $point): bool
    {
        return $this->time()->aheadOf($point);
    }

    public function toString(): string
    {
        return $this->time()->toString();
    }

    private function time(): PointInTime
    {
        return $this->clock->at($this->time);
    }
}
