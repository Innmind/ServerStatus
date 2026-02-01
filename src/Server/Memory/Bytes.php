<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Memory;

use Innmind\IO\Stream\Size;
use Innmind\Validation\Is;
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

    /**
     * @param int<0, max> $value
     */
    private function __construct(
        private int $value,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param int<0, max> $value
     */
    public static function of(int $value): self
    {
        return new self($value);
    }

    /**
     * @return int<0, max>
     */
    #[\NoDiscard]
    public function toInt(): int
    {
        return $this->value;
    }

    #[\NoDiscard]
    public function toString(): string
    {
        return Size::of($this->value)->toString();
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    public static function maybe(string $bytes): Maybe
    {
        if ($bytes === (string) (int) $bytes) {
            return Maybe::just((int) $bytes)
                ->keep(
                    Is::int()
                        ->positive()
                        ->or(Is::value(0))
                        ->asPredicate(),
                )
                ->map(static fn($value) => new self($value));
        }

        return Str::of($bytes)
            ->maybe(static fn($bytes) => $bytes->length() >= 2)
            ->flatMap(static fn($bytes) => self::attemptLinux($bytes)->otherwise(
                static fn() => self::attemptDarwin($bytes),
            ));
    }

    /**
     * @psalm-pure
     *
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
     * @psalm-pure
     *
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
     * @psalm-pure
     *
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
            ->keep(
                Is::int()
                    ->positive()
                    ->or(Is::value(0))
                    ->asPredicate(),
            )
            ->map(static fn($bytes) => new self($bytes));
    }
}
