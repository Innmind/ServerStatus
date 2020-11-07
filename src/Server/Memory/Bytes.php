<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Memory;

use Innmind\Server\Status\Exception\{
    BytesCannotBeNegative,
    UnknownBytesFormat,
};
use Innmind\Immutable\Str;

final class Bytes
{
    public const BYTES = 1024;
    public const KILOBYTES = 1024 ** 2;
    public const MEGABYTES = 1024 ** 3;
    public const GIGABYTES = 1024 ** 4;
    public const TERABYTES = 1024 ** 5;
    public const PETABYTES = 1024 ** 6;

    private int $value;
    private string $string;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new BytesCannotBeNegative((string) $value);
        }

        $this->value = $value;
        $this->string = $value.'B';

        switch (true) {
            case $value < self::BYTES:
                $this->string = $value.'B';
                break;

            case $value < self::KILOBYTES:
                $this->string = \sprintf(
                    '%sKB',
                    \round($value/self::BYTES, 3),
                );
                break;

            case $value < self::MEGABYTES:
                $this->string = \sprintf(
                    '%sMB',
                    \round($value/self::KILOBYTES, 3),
                );
                break;

            case $value < self::GIGABYTES:
                $this->string = \sprintf(
                    '%sGB',
                    \round($value/self::MEGABYTES, 3),
                );
                break;

            case $value < self::TERABYTES:
                $this->string = \sprintf(
                    '%sTB',
                    \round($value/self::GIGABYTES, 3),
                );
                break;

            case $value < self::PETABYTES:
                $this->string = \sprintf(
                    '%sPB',
                    \round($value/self::TERABYTES, 3),
                );
                break;
        }
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->string;
    }

    public static function of(string $bytes): self
    {
        if ($bytes === (string) (int) $bytes) {
            return new self((int) $bytes);
        }

        $bytes = Str::of($bytes);

        if ($bytes->length() < 2) {
            throw new UnknownBytesFormat($bytes->toString());
        }

        try {
            return self::fromUnit(
                $bytes->substring(0, -1),
                $bytes->substring(-1),
            );
        } catch (UnknownBytesFormat $e) {
            return self::fromUnit(
                $bytes->substring(0, -2),
                $bytes->substring(-2),
            );
        }
    }

    private static function fromUnit(Str $bytes, Str $unit): self
    {
        if ($bytes->length() === 0) {
            throw new UnknownBytesFormat($bytes->toString());
        }

        switch ($unit->toString()) {
            case 'B':
            case 'Bi':
                $multiplier = 1;
                break;

            case 'K':
            case 'Ki':
                $multiplier = self::BYTES;
                break;

            case 'M':
            case 'Mi':
                $multiplier = self::KILOBYTES;
                break;

            case 'G':
            case 'Gi':
                $multiplier = self::MEGABYTES;
                break;

            case 'T':
            case 'Ti':
                $multiplier = self::GIGABYTES;
                break;

            case 'P':
            case 'Pi':
                $multiplier = self::TERABYTES;
                break;

            default:
                throw new UnknownBytesFormat($bytes->toString());
        }

        return new self(
            (int) (((float) $bytes->toString()) * $multiplier),
        );
    }
}
