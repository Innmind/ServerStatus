<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk\Volume;

use Innmind\Server\Status\{
    Server\Disk\Volume\Usage,
    Exception\OutOfBoundsPercentage,
};
use PHPUnit\Framework\TestCase;

class UsageTest extends TestCase
{
    public function testInterface()
    {
        $usage = new Usage(42.24);

        $this->assertSame(42.24, $usage->toFloat());
        $this->assertSame('42.24%', $usage->toString());
    }

    public function testThrowWhenUsageLowerThanZero()
    {
        $this->expectException(OutOfBoundsPercentage::class);

        new Usage(-1);
    }

    public function testThrowWhenUsageHigherThanHundred()
    {
        $this->expectException(OutOfBoundsPercentage::class);

        new Usage(101);
    }
}
