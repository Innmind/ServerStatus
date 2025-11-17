<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Process\Pid;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};

interface Processes
{
    /**
     * @return Sequence<Process>
     */
    public function all(): Sequence;

    /**
     * @return Maybe<Process>
     */
    public function get(Pid $pid): Maybe;
}
