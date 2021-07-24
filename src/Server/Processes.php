<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
};
use Innmind\Immutable\{
    Map,
    Maybe,
};

interface Processes
{
    /**
     * @return Map<int, Process>
     */
    public function all(): Map;

    /**
     * @return Maybe<Process>
     */
    public function get(Pid $pid): Maybe;
}
