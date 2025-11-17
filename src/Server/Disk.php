<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Disk\{
    Volume,
    Volume\MountPoint,
};
use Innmind\Immutable\{
    Sequence,
    Maybe,
};

interface Disk
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
