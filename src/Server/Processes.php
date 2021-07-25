<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
};
use Innmind\Immutable\{
    Set,
    Maybe,
};

interface Processes
{
    /**
     * @return Set<Process>
     */
    public function all(): Set;

    /**
     * @return Maybe<Process>
     */
    public function get(Pid $pid): Maybe;
}
