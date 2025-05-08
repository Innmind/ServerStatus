<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Cpu;

use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class Percentage
{
    private function __construct(
        private float $value,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    public static function maybe(float $value): Maybe
    {
        if ($value < 0) {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        return Maybe::just(new self($value));
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function toString(): string
    {
        return \sprintf(
            '%s%%',
            $this->value,
        );
    }
}
