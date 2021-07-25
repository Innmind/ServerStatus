<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
    Process\User,
    Process\Command,
    Process\Memory,
    Cpu\Percentage,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Maybe;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    public function testInterface()
    {
        $process = new Process(
            $pid = new Pid(1),
            $user = new User('root'),
            $cpu = new Percentage(42),
            $memory = new Memory(42),
            $start = Maybe::just($this->createMock(PointInTime::class)),
            $command = new Command('/sbin/launchd')
        );

        $this->assertSame($pid, $process->pid());
        $this->assertSame($user, $process->user());
        $this->assertSame($cpu, $process->cpu());
        $this->assertSame($memory, $process->memory());
        $this->assertSame($start, $process->start());
        $this->assertSame($command, $process->command());
    }
}
