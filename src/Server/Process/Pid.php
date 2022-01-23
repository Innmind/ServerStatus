<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Exception\LowestPidPossibleIsOne;

/**
 * @psalm-immutable
 */
final class Pid
{
    private int $value;

    /**
     * @throws LowestPidPossibleIsOne
     */
    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new LowestPidPossibleIsOne((string) $value);
        }

        $this->value = $value;
    }

    public function equals(self $pid): bool
    {
        return $pid->is($this->value);
    }

    public function is(int $value): bool
    {
        return $this->value === $value;
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }
}
