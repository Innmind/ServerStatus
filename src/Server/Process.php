<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process\Pid,
    Process\User,
    Process\Command,
    Process\Memory,
    Cpu\Percentage
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
        Memory $memory,
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

    public function memory(): Memory
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
