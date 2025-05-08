<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk\Volume;

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
     * @param non-empty-string $value
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    public function equals(self $point): bool
    {
        return $point->is($this->value);
    }

    public function is(string $point): bool
    {
        return $this->value === $point;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
