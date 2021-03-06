<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Cpu;

use Innmind\Server\Status\Exception\OutOfBoundsPercentage;

final class Percentage
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < 0) {
            throw new OutOfBoundsPercentage((string) $value);
        }

        $this->value = $value;
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value.'%';
    }
}
