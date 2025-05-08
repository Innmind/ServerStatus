<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\LoadAverage;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LoadAverageTest extends TestCase
{
    public function testInterface()
    {
        $load = LoadAverage::maybe(1, 5, 15)->match(
            static fn($load) => $load,
            static fn() => null,
        );

        $this->assertNotNull($load);
        $this->assertSame(1.0, $load->lastMinute());
        $this->assertSame(5.0, $load->lastFiveMinutes());
        $this->assertSame(15.0, $load->lastFifteenMinutes());
    }

    public function testReturnNothingWhenNegativeLastMinuteLoad()
    {
        $this->assertNull(LoadAverage::maybe(-1, 5, 15)->match(
            static fn($load) => $load,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenNegativeLastFiveMinuteLoad()
    {
        $this->assertNull(LoadAverage::maybe(1, -5, 15)->match(
            static fn($load) => $load,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenNegativeLastFifteenMinuteLoad()
    {
        $this->assertNull(LoadAverage::maybe(1, 5, -15)->match(
            static fn($load) => $load,
            static fn() => null,
        ));
    }
}
