<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk\Volume;

use Innmind\Server\Status\Exception\EmptyPathNotAllowed;

final class MountPoint
{
    private string $value;

    public function __construct(string $value)
    {
        if ($value === '') {
            throw new EmptyPathNotAllowed;
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
