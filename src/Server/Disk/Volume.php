<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\Server\{
    Disk\Volume\MountPoint,
    Disk\Volume\Usage,
    Memory\Bytes
};

final class Volume
{
    private $mountPoint;
    private $size;
    private $available;
    private $used;
    private $usage;

    public function __construct(
        MountPoint $mountPoint,
        Bytes $size,
        Bytes $available,
        Bytes $used,
        Usage $usage
    ) {
        $this->mountPoint = $mountPoint;
        $this->size = $size;
        $this->available = $available;
        $this->used = $used;
        $this->usage = $usage;
    }

    public function mountPoint(): MountPoint
    {
        return $this->mountPoint;
    }

    public function size(): Bytes
    {
        return $this->size;
    }

    public function available(): Bytes
    {
        return $this->available;
    }

    public function used(): Bytes
    {
        return $this->used;
    }

    public function usage(): Usage
    {
        return $this->usage;
    }
}
