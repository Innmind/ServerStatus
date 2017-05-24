<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Exception\LowestPidPossibleIsOne;

final class Pid
{
    private $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new LowestPidPossibleIsOne;
        }

        $this->value = $value;
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
