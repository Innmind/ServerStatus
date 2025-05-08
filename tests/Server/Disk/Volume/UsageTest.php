<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk\Volume;

use Innmind\Server\Status\Server\Disk\Volume\Usage;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class UsageTest extends TestCase
{
    public function testInterface()
    {
        $usage = Usage::maybe(42.24)->match(
            static fn($usage) => $usage,
            static fn() => null,
        );

        $this->assertNotNull($usage);
        $this->assertSame(42.24, $usage->toFloat());
        $this->assertSame('42.24%', $usage->toString());
    }

    public function testReturnNothingWhenUsageLowerThanZero()
    {
        $this->assertNull(Usage::maybe(-1)->match(
            static fn($usage) => $usage,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenUsageHigherThanHundred()
    {
        $this->assertNull(Usage::maybe(101)->match(
            static fn($usage) => $usage,
            static fn() => null,
        ));
    }
}
