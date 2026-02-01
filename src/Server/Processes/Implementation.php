<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
};
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
     * @return Sequence<Process>
     */
    public function all(): Sequence;

    /**
     * @return Maybe<Process>
     */
    public function get(Pid $pid): Maybe;
}
