<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

use Innmind\Immutable\{
    RegExp,
    Str,
};

/**
 * @psalm-immutable
 */
final class Command
{
    /**
     * @param non-empty-string $value
     */
    private function __construct(
        private string $value,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param non-empty-string $value
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    #[\NoDiscard]
    public function matches(RegExp $pattern): bool
    {
        return $pattern->matches(Str::of($this->value));
    }

    /**
     * @return non-empty-string
     */
    #[\NoDiscard]
    public function toString(): string
    {
        return $this->value;
    }
}
