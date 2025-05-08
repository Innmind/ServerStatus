<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Cpu\{
    Percentage,
    Cores,
};

/**
 * @psalm-immutable
 */
final class Cpu
{
    private function __construct(
        private Percentage $user,
        private Percentage $system,
        private Percentage $idle,
        private Cores $cores,
    ) {
    }

    /**
     * @psalm-pure
     */
    public static function of(
        Percentage $user,
        Percentage $system,
        Percentage $idle,
        Cores $cores,
    ): self {
        return new self($user, $system, $idle, $cores);
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

    public function cores(): Cores
    {
        return $this->cores;
    }

    public function toString(): string
    {
        return \sprintf(
            'CPU usage: %s user, %s sys, %s idle',
            $this->user->toString(),
            $this->system->toString(),
            $this->idle->toString(),
        );
    }
}
