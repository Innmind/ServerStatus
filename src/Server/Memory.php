<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Memory\Bytes;

/**
 * @psalm-immutable
 */
final class Memory
{
    private function __construct(
        private Bytes $total,
        private Bytes $active,
        private Bytes $free,
        private Bytes $swap,
        private Bytes $used,
    ) {
    }

    /**
     * @psalm-pure
     */
    public static function of(
        Bytes $total,
        Bytes $active,
        Bytes $free,
        Bytes $swap,
        Bytes $used,
    ): self {
        return new self($total, $active, $free, $swap, $used);
    }

    public function total(): Bytes
    {
        return $this->total;
    }

    public function active(): Bytes
    {
        return $this->active;
    }

    public function free(): Bytes
    {
        return $this->free;
    }

    public function swap(): Bytes
    {
        return $this->swap;
    }

    public function used(): Bytes
    {
        return $this->used;
    }
}
