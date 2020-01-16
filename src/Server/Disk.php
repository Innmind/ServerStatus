<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Disk\{
    Volume,
    Volume\MountPoint,
};
use Innmind\Immutable\Map;

interface Disk
{
    /**
     * @return Map<string, Volume>
     */
    public function volumes(): Map;
    public function get(MountPoint $point): Volume;
}
