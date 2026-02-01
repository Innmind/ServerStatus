<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

/**
 * @psalm-immutable
 */
final class Pid
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

    #[\NoDiscard]
    public function equals(self $pid): bool
    {
        return $pid->is($this->value);
    }

    #[\NoDiscard]
    public function is(int $value): bool
    {
        return $this->value === $value;
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
