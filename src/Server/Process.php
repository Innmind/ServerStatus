<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process\Pid,
    Process\User,
    Process\Command,
    Cpu\Percentage,
    Memory\Bytes
};
use Innmind\TimeContinuum\PointInTimeInterface;

final class Process
{
    private $pid;
    private $user;
    private $cpu;
    private $memory;
    private $start;
    private $command;

    public function __construct(
        Pid $pid,
        User $user,
        Percentage $cpu,
        Bytes $memory,
        PointInTimeInterface $start,
        Command $command
    ) {
        $this->pid = $pid;
        $this->user = $user;
        $this->cpu = $cpu;
        $this->memory = $memory;
        $this->start = $start;
        $this->command = $command;
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

    public function memory(): Bytes
    {
        return $this->memory;
    }

    public function start(): PointInTimeInterface
    {
        return $this->start;
    }

    public function command(): Command
    {
        return $this->command;
    }
}
