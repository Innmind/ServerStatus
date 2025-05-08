<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\{
    Server\Process\Pid,
    Exception\LowestPidPossibleIsOne,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class PidTest extends TestCase
{
    public function testInterface()
    {
        $pid = new Pid(42);

        $this->assertSame(42, $pid->toInt());
        $this->assertSame('42', $pid->toString());
    }

    public function testThrowWhenPidTooLow()
    {
        $this->expectException(LowestPidPossibleIsOne::class);

        new Pid(0);
    }
}
