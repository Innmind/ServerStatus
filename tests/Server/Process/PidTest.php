<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\Pid;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class PidTest extends TestCase
{
    public function testInterface()
    {
        $pid = Pid::of(42);

        $this->assertSame(42, $pid->toInt());
        $this->assertSame('42', $pid->toString());
    }
}
