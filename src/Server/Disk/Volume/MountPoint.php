<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk\Volume;

/**
 * @psalm-immutable
 */
final class MountPoint
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
    public function equals(self $point): bool
    {
        return $point->is($this->value);
    }

    #[\NoDiscard]
    public function is(string $point): bool
    {
        return $this->value === $point;
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
