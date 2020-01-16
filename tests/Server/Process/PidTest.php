<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\Pid;
use PHPUnit\Framework\TestCase;

class PidTest extends TestCase
{
    public function testInterface()
    {
        $pid = new Pid(42);

        $this->assertSame(42, $pid->toInt());
        $this->assertSame('42', $pid->toString());
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\LowestPidPossibleIsOne
     */
    public function testThrowWhenPidTooLow()
    {
        new Pid(0);
    }
}
