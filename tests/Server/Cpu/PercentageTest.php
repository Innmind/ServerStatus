<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Cpu;

use Innmind\Server\Status\Server\Cpu\Percentage;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class PercentageTest extends TestCase
{
    public function testInterface()
    {
        $percentage = Percentage::maybe(42.24)->match(
            static fn($percentage) => $percentage,
            static fn() => null,
        );

        $this->assertNotNull($percentage);
        $this->assertSame(42.24, $percentage->toFloat());
        $this->assertSame('42.24%', $percentage->toString());
    }

    public function testReturnNothingWhenPercentageLowerThanZero()
    {
        $this->assertNull(Percentage::maybe(-1)->match(
            static fn($percentage) => $percentage,
            static fn() => null,
        ));
    }
}
