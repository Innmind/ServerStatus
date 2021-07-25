<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Disk\{
    Volume,
    Volume\MountPoint,
};
use Innmind\Immutable\{
    Set,
    Maybe,
};

interface Disk
{
    /**
     * @return Set<Volume>
     */
    public function volumes(): Set;

    /**
     * @return Maybe<Volume>
     */
    public function get(MountPoint $point): Maybe;
}
