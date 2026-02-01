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
     * @internal
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

    #[\NoDiscard]
    public function user(): Percentage
    {
        return $this->user;
    }

    #[\NoDiscard]
    public function system(): Percentage
    {
        return $this->system;
    }

    #[\NoDiscard]
    public function idle(): Percentage
    {
        return $this->idle;
    }

    #[\NoDiscard]
    public function cores(): Cores
    {
        return $this->cores;
    }

    #[\NoDiscard]
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
