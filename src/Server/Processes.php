<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
};
use Innmind\Immutable\Map;

interface Processes
{
    /**
     * @return Map<int, Process>
     */
    public function all(): Map;
    public function get(Pid $pid): Process;
}
