<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

/**
 * @psalm-immutable
 */
final class EnvironmentPath
{
    private function __construct(
        private string $value,
    ) {
    }

    /**
     * @psalm-pure
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
