<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\LoadAverage;
use PHPUnit\Framework\TestCase;

class LoadAverageTest extends TestCase
{
    public function testInterface()
    {
        $load = new LoadAverage(1, 5, 15);

        $this->assertSame(1, $load->lastMinute());
        $this->assertSame(5, $load->lastFiveMinutes());
        $this->assertSame(15, $load->lastFifteenMinutes());
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\LoadAverageCannotBeNegative
     */
    public function testThrowWhenNegativeLastMinuteLoad()
    {
        new LoadAverage(-1, 5, 15);
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\LoadAverageCannotBeNegative
     */
    public function testThrowWhenNegativeLastFiveMinuteLoad()
    {
        new LoadAverage(1, -5, 15);
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\LoadAverageCannotBeNegative
     */
    public function testThrowWhenNegativeLastFifteenMinuteLoad()
    {
        new LoadAverage(1, 5, -15);
    }
}
