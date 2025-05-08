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
use Fixtures\Innmind\TimeContinuum\PointInTime;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
};

class ProcessTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(PointInTime::any())
            ->then(function($pointInTime) {
                $process = new Process(
                    $pid = Pid::of(1),
                    $user = User::of('root'),
                    $cpu = Percentage::maybe(42)->match(
                        static fn($percentage) => $percentage,
                        static fn() => throw new \Exception('Should be valid'),
                    ),
                    $memory = Memory::maybe(42)->match(
                        static fn($memory) => $memory,
                        static fn() => throw new \Exception('Should be valid'),
                    ),
                    $start = Maybe::just($pointInTime),
                    $command = Command::of('/sbin/launchd'),
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
