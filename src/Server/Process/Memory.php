<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Exception\OutOfBoundsPercentage;

/**
 * @psalm-immutable
 */
final class Memory
{
    private float $value;

    /**
     * @throws OutOfBoundsPercentage
     */
    public function __construct(float $value)
    {
        if ($value < 0 || $value > 100) {
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
