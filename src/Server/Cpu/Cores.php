<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Cpu;

use Innmind\Server\Status\Exception\DomainException;

/**
 * @psalm-immutable
 */
final class Cores
{
    private int $value;

    /**
     * @throws DomainException
     */
    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new DomainException((string) $value);
        }

        $this->value = $value;
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
