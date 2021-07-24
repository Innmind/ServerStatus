<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Disk\{
    Volume,
    Volume\MountPoint,
};
use Innmind\Immutable\{
    Map,
    Maybe,
};

interface Disk
{
    /**
     * @return Map<string, Volume>
     */
    public function volumes(): Map;

    /**
     * @return Maybe<Volume>
     */
    public function get(MountPoint $point): Maybe;
}
