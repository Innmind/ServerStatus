<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\Server\{
    Disk\Volume\MountPoint,
    Disk\Volume\Usage,
    Memory\Bytes,
};

/**
 * @psalm-immutable
 */
final class Volume
{
    private function __construct(
        private MountPoint $mountPoint,
        private Bytes $size,
        private Bytes $available,
        private Bytes $used,
        private Usage $usage,
    ) {
    }

    /**
     * @psalm-pure
     */
    public static function of(
        MountPoint $mountPoint,
        Bytes $size,
        Bytes $available,
        Bytes $used,
        Usage $usage,
    ): self {
        return new self($mountPoint, $size, $available, $used, $usage);
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
