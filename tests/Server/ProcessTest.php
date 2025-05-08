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
use Innmind\Immutable\Maybe;
use Fixtures\Innmind\TimeContinuum\Earth\PointInTime;
use Innmind\BlackBox\PHPUnit\BlackBox;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(PointInTime::any())
            ->then(function($pointInTime) {
                $process = new Process(
                    $pid = new Pid(1),
                    $user = new User('root'),
                    $cpu = new Percentage(42),
                    $memory = new Memory(42),
                    $start = Maybe::just($pointInTime),
                    $command = new Command('/sbin/launchd'),
                );

                $this->assertSame($pid, $process->pid());
                $this->assertSame($user, $process->user());
                $this->assertSame($cpu, $process->cpu());
                $this->assertSame($memory, $process->memory());
                $this->assertSame($start, $process->start());
                $this->assertSame($command, $process->command());
            });
    }
}
