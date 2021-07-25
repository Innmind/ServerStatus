<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Memory\Bytes;

/**
 * @psalm-immutable
 */
final class Memory
{
    private Bytes $total;
    private Bytes $wired;
    private Bytes $active;
    private Bytes $free;
    private Bytes $swap;
    private Bytes $used;

    public function __construct(
        Bytes $total,
        Bytes $wired,
        Bytes $active,
        Bytes $free,
        Bytes $swap,
        Bytes $used,
    ) {
        $this->total = $total;
        $this->wired = $wired;
        $this->active = $active;
        $this->free = $free;
        $this->swap = $swap;
        $this->used = $used;
    }

    public function total(): Bytes
    {
        return $this->total;
    }

    public function wired(): Bytes
    {
        return $this->wired;
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
