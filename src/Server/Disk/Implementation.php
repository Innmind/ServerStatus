<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\Server\Disk\Volume\MountPoint;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};

/**
 * @internal
 */
interface Implementation
{
    /**
     * @return Sequence<Volume>
     */
    public function volumes(): Sequence;

    /**
     * @return Maybe<Volume>
     */
    public function get(MountPoint $point): Maybe;
}
