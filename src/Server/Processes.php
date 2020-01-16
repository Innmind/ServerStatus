<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
};
use Innmind\Immutable\MapInterface;

interface Processes
{
    /**
     * @return MapInterface<int, Process>
     */
    public function all(): MapInterface;
    public function get(Pid $pid): Process;
}
