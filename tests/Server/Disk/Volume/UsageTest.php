<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk\Volume;

use Innmind\Server\Status\Server\Disk\Volume\Usage;
use PHPUnit\Framework\TestCase;

class UsageTest extends TestCase
{
    public function testInterface()
    {
        $usage = new Usage(42.24);

        $this->assertSame(42.24, $usage->toFloat());
        $this->assertSame('42.24%', (string) $usage);
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\OutOfBoundsPercentage
     */
    public function testThrowWhenUsageLowerThanZero()
    {
        new Usage(-1);
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\OutOfBoundsPercentage
     */
    public function testThrowWhenUsageHigherThanHundred()
    {
        new Usage(101);
    }
}
