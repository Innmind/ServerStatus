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
     * @internal
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

    #[\NoDiscard]
    public function mountPoint(): MountPoint
    {
        return $this->mountPoint;
    }

    #[\NoDiscard]
    public function size(): Bytes
    {
        return $this->size;
    }

    #[\NoDiscard]
    public function available(): Bytes
    {
        return $this->available;
    }

    #[\NoDiscard]
    public function used(): Bytes
    {
        return $this->used;
    }

    #[\NoDiscard]
    public function usage(): Usage
    {
        return $this->usage;
    }
}
