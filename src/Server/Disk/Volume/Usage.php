<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk\Volume;

use Innmind\Server\Status\Exception\OutOfBoundsPercentage;

final class Usage
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < 0 || $value > 100) {
            throw new OutOfBoundsPercentage;
        }

        $this->value = $value;
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value.'%';
    }
}
