<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\{
    Server\LoadAverage,
    Exception\LoadAverageCannotBeNegative,
};
use PHPUnit\Framework\TestCase;

class LoadAverageTest extends TestCase
{
    public function testInterface()
    {
        $load = new LoadAverage(1, 5, 15);

        $this->assertSame(1.0, $load->lastMinute());
        $this->assertSame(5.0, $load->lastFiveMinutes());
        $this->assertSame(15.0, $load->lastFifteenMinutes());
    }

    public function testThrowWhenNegativeLastMinuteLoad()
    {
        $this->expectException(LoadAverageCannotBeNegative::class);

        new LoadAverage(-1, 5, 15);
    }

    public function testThrowWhenNegativeLastFiveMinuteLoad()
    {
        $this->expectException(LoadAverageCannotBeNegative::class);

        new LoadAverage(1, -5, 15);
    }

    public function testThrowWhenNegativeLastFifteenMinuteLoad()
    {
        $this->expectException(LoadAverageCannotBeNegative::class);

        new LoadAverage(1, 5, -15);
    }
}
