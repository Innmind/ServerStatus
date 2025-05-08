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
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class Process
{
    /**
     * @param Maybe<PointInTime> $start
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
     * @param Maybe<PointInTime> $start
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

    public function pid(): Pid
    {
        return $this->pid;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function cpu(): Percentage
    {
        return $this->cpu;
    }

    public function memory(): Memory
    {
        return $this->memory;
    }

    /**
     * @return Maybe<PointInTime>
     */
    public function start(): Maybe
    {
        return $this->start;
    }

    public function command(): Command
    {
        return $this->command;
    }
}
