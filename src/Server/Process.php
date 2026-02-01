<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process\Pid,
    Process\User,
    Process\Command,
    Process\Memory,
    Cpu\Percentage,
};
use Innmind\Time\Point;
use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class Process
{
    /**
     * @param Maybe<Point> $start
     */
    private function __construct(
        private Pid $pid,
        private User $user,
        private Percentage $cpu,
        private Memory $memory,
        private Maybe $start,
        private Command $command,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Maybe<Point> $start
     */
    public static function of(
        Pid $pid,
        User $user,
        Percentage $cpu,
        Memory $memory,
        Maybe $start,
        Command $command,
    ): self {
        return new self($pid, $user, $cpu, $memory, $start, $command);
    }

    #[\NoDiscard]
    public function pid(): Pid
    {
        return $this->pid;
    }

    #[\NoDiscard]
    public function user(): User
    {
        return $this->user;
    }

    #[\NoDiscard]
    public function cpu(): Percentage
    {
        return $this->cpu;
    }

    #[\NoDiscard]
    public function memory(): Memory
    {
        return $this->memory;
    }

    /**
     * @return Maybe<Point>
     */
    #[\NoDiscard]
    public function start(): Maybe
    {
        return $this->start;
    }

    #[\NoDiscard]
    public function command(): Command
    {
        return $this->command;
    }
}
