<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Exception\EmptyCommandNotAllowed;
use Innmind\Immutable\{
    RegExp,
    Str,
};

final class Command
{
    private string $value;

    public function __construct(string $value)
    {
        if ($value === '') {
            throw new EmptyCommandNotAllowed;
        }

        $this->value = $value;
    }

    public function matches(RegExp $pattern): bool
    {
        return $pattern->matches(Str::of($this->value));
    }

    public function toString(): string
    {
        return $this->value;
    }
}
