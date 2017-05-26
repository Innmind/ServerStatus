<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Memory;

use Innmind\Server\Status\Exception\{
    BytesCannotBeNegative,
    UnknownBytesFormat
};
use Innmind\Immutable\Str;

final class Bytes
{
    private const BYTES = 1024;
    private const KILOBYTES = 1024 ** 2;
    private const MEGABYTES = 1024 ** 3;
    private const GIGABYTES = 1024 ** 4;
    private const TERABYTES = 1024 ** 5;
    private const PETABYTES = 1024 ** 6;

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

    public static function fromString(string $bytes): self
    {
        $bytes = new Str($bytes);

        try {
            return self::fromUnit(
                $bytes->substring(0, -1),
                $bytes->substring(-1)
            );
        } catch (UnknownBytesFormat $e) {
            return self::fromUnit(
                $bytes->substring(0, -2),
                $bytes->substring(-2)
            );
        }
    }

    private static function fromUnit(Str $bytes, Str $unit): self
    {
        switch ((string) $unit) {
            case 'K':
            case 'Ki':
                $multiplier = Bytes::BYTES;
                break;

            case 'M':
            case 'Mi':
                $multiplier = Bytes::KILOBYTES;
                break;

            case 'G':
            case 'Gi':
                $multiplier = Bytes::MEGABYTES;
                break;

            case 'T':
            case 'Ti':
                $multiplier = Bytes::GIGABYTES;
                break;

            case 'P':
            case 'Pi':
                $multiplier = Bytes::TERABYTES;
                break;

            default:
                throw new UnknownBytesFormat;
        }

        return new self(
            (int) (((float) (string) $bytes) * $multiplier)
        );
    }
}
