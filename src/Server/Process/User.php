<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Exception\EmptyUserNotAllowed;

final class User
{
    private $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new EmptyUserNotAllowed;
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
