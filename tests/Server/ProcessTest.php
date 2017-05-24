<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\{
    Process,
    Process\Pid,
    Process\User,
    Process\Command,
    Cpu\Percentage,
    Memory\Bytes
};
use Innmind\TimeContinuum\PointInTimeInterface;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    public function testInterface()
    {
        $process = new Process(
            $pid = new Pid(1),
            $user = new User('root'),
            $cpu = new Percentage(42),
            $memory = new Bytes(42),
            $start = $this->createMock(PointInTimeInterface::class),
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
