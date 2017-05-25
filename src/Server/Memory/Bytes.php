<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Memory;

use Innmind\Server\Status\Exception\BytesCannotBeNegative;

final class Bytes
{
    public const BYTES = 1024;
    public const KILOBYTES = 1024 ** 2;
    public const MEGABYTES = 1024 ** 3;
    public const GIGABYTES = 1024 ** 4;
    public const TERABYTES = 1024 ** 5;
    public const PETABYTES = 1024 ** 6;

    private $value;
    private $string;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new BytesCannotBeNegative;
        }

        $this->value = $value;

        switch (true) {
            case $value < self::BYTES:
                $this->string = $value.'B';
                break;

            case $value < self::KILOBYTES:
                $this->string = sprintf(
                    '%sKB',
                    round($value/self::BYTES, 3)
                );
                break;

            case $value < self::MEGABYTES:
                $this->string = sprintf(
                    '%sMB',
                    round($value/self::KILOBYTES, 3)
                );
                break;

            case $value < self::GIGABYTES:
                $this->string = sprintf(
                    '%sGB',
                    round($value/self::MEGABYTES, 3)
                );
                break;

            case $value < self::TERABYTES:
                $this->string = sprintf(
                    '%sTB',
                    round($value/self::GIGABYTES, 3)
                );
                break;

            case $value < self::PETABYTES:
                $this->string = sprintf(
                    '%sPB',
                    round($value/self::TERABYTES, 3)
                );
                break;
        }
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
