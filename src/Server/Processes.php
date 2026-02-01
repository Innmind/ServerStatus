<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Processes\Implementation,
    Processes\Unix,
    Processes\Logger,
    Process\Pid,
};
use Innmind\Server\Control\Server as Control;
use Innmind\Time\Clock;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};
use Psr\Log\LoggerInterface;

final class Processes
{
    private function __construct(
        private Implementation $implementation,
    ) {
    }

    /**
     * @internal
     */
    public static function osx(Clock $clock, Control\Processes $processes): self
    {
        return new self(Unix::osx($clock, $processes));
    }

    /**
     * @internal
     */
    public static function linux(Clock $clock, Control\Processes $processes): self
    {
        return new self(Unix::linux($clock, $processes));
    }

    /**
     * @internal
     */
    public static function logger(self $processes, LoggerInterface $logger): self
    {
        return new self(Logger::of(
            $processes->implementation,
            $logger,
        ));
    }

    /**
     * @return Sequence<Process>
     */
    public function all(): Sequence
    {
        return $this->implementation->all();
    }

    /**
     * @return Maybe<Process>
     */
    public function get(Pid $pid): Maybe
    {
        return $this->implementation->get($pid);
    }
}
