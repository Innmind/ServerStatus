<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Exception\EmptyUserNotAllowed;

/**
 * @psalm-immutable
 */
final class User
{
    private string $value;

    /**
     * @throws EmptyUserNotAllowed
     */
    public function __construct(string $value)
    {
        if ($value === '') {
            throw new EmptyUserNotAllowed;
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
