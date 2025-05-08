<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Memory;

use Innmind\Server\Status\Exception\BytesCannotBeNegative;
use Innmind\Immutable\{
    Str,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Bytes
{
    private const BYTES = 1024;
    private const KILOBYTES = 1024 ** 2;
    private const MEGABYTES = 1024 ** 3;
    private const GIGABYTES = 1024 ** 4;
    private const TERABYTES = 1024 ** 5;
    private const PETABYTES = 1024 ** 6;

    private int $value;
    private string $string;

    /**
     * @throws BytesCannotBeNegative
     */
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

    /**
     * @return Maybe<self>
     */
    public static function of(string $bytes): Maybe
    {
        if ($bytes === (string) (int) $bytes) {
            return Maybe::just(new self((int) $bytes));
        }

        return Maybe::just(Str::of($bytes))
            ->filter(static fn($bytes) => $bytes->length() >= 2)
            ->flatMap(static fn($bytes) => self::attemptLinux($bytes)->otherwise(
                static fn() => self::attemptDarwin($bytes),
            ));
    }

    /**
     * @return Maybe<self>
     */
    private static function attemptDarwin(Str $bytes): Maybe
    {
        return self::fromUnit(
            $bytes->dropEnd(2),
            $bytes->takeEnd(2),
        );
    }

    /**
     * @return Maybe<self>
     */
    private static function attemptLinux(Str $bytes): Maybe
    {
        return self::fromUnit(
            $bytes->dropEnd(1),
            $bytes->takeEnd(1),
        );
    }

    /**
     * @return Maybe<self>
     */
    private static function fromUnit(Str $bytes, Str $unit): Maybe
    {
        if ($bytes->length() === 0) {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        $multiplier = match ($unit->toString()) {
            'B' => 1,
            'Bi' => 1,
            'K' => self::BYTES,
            'Ki' => self::BYTES,
            'M' => self::KILOBYTES,
            'Mi' => self::KILOBYTES,
            'G' => self::MEGABYTES,
            'Gi' => self::MEGABYTES,
            'T' => self::GIGABYTES,
            'Ti' => self::GIGABYTES,
            'P' => self::TERABYTES,
            'Pi' => self::TERABYTES,
            default => null,
        };

        return Maybe::of($multiplier)
            ->map(static fn($multiplier) => (int) (((float) $bytes->toString()) * (float) $multiplier))
            ->map(static fn($bytes) => new self($bytes));
    }
}
