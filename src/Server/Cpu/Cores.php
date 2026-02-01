<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Cpu;

/**
 * @psalm-immutable
 */
final class Cores
{
    /**
     * @param int<1, max> $value
     */
    private function __construct(
        private int $value,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param int<1, max> $value
     */
    public static function of(int $value): self
    {
        return new self($value);
    }

    /**
     * @return int<1, max>
     */
    #[\NoDiscard]
    public function toInt(): int
    {
        return $this->value;
    }

    #[\NoDiscard]
    public function toString(): string
    {
        return (string) $this->value;
    }
}
