<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Memory\Bytes;

final class Memory
{
    private $total;
    private $wired;
    private $active;
    private $free;
    private $swap;
    private $used;

    public function __construct(
        Bytes $total,
        Bytes $wired,
        Bytes $active,
        Bytes $free,
        Bytes $swap,
        Bytes $used
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
