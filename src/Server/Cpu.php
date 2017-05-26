<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Cpu\Percentage;

final class Cpu
{
    private $user;
    private $system;
    private $idle;

    public function __construct(
        Percentage $user,
        Percentage $system,
        Percentage $idle
    ) {
        $this->user = $user;
        $this->system = $system;
        $this->idle = $idle;
    }

    public function user(): Percentage
    {
        return $this->user;
    }

    public function system(): Percentage
    {
        return $this->system;
    }

    public function idle(): Percentage
    {
        return $this->idle;
    }

    public function __toString(): string
    {
        return sprintf(
            'CPU usage: %s user, %s sys, %s idle',
            $this->user,
            $this->system,
            $this->idle
        );
    }
}
