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
     * @psalm-pure
     *
     * @param int<1, max> $value
     */
    public static function of(int $value): self
    {
        return new self($value);
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
