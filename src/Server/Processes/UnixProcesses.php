<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Processes;

use Innmind\Server\Status\{
    Server\Processes,
    Server\Process,
    Server\Process\Pid,
    Server\Process\User,
    Server\Process\Command,
    Server\Process\Memory,
    Server\Cpu\Percentage,
    Exception\InformationNotAccessible,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\{
    Str,
    Sequence,
    Set,
    Maybe,
};
use function Innmind\Immutable\join;
use Symfony\Component\Process\Process as SfProcess;

final class UnixProcesses implements Processes
{
    private Clock $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function all(): Set
    {
        return $this->parse(
            $this->run('ps -eo '.$this->format()),
        );
    }

    public function get(Pid $pid): Maybe
    {
        try {
            $processes = $this->parse(
                $this->run(\sprintf('ps -o %s -p %s', $this->format(), $pid->toString())),
            );
        } catch (InformationNotAccessible $e) {
            $processes = $this->parse(
                $this->run(\sprintf('ps -o %s -q %s', $this->format(), $pid->toString())),
            );
        }

        return $processes->find(static fn($process) => $process->pid()->equals($pid));
    }

    private function run(string $command): Str
    {
        $process = SfProcess::fromShellCommandline($command, null, [
            'TZ' => \date('e'),
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new InformationNotAccessible;
        }

        return Str::of($process->getOutput());
    }

    /**
     * @return Set<Process>
     */
    private function parse(Str $output): Set
    {
        $lines = $output
            ->trim()
            ->split("\n");

        $partsByLine = $lines
            ->drop(1) // columns name
            ->map(static fn($line) => $line->pregSplit('~ +~', 10)); // 6 columns + 4 spaces in the START column
        $processes = $partsByLine->map(function(Sequence $parts): Process {
            $startParts = $parts
                ->take(5)
                ->map(static fn(Str $part): string => $part->toString());
            $start = join(' ', $startParts)->toString();
            $parts = $parts
                ->drop(5)
                ->map(static fn($part) => $part->toString());
            $user = $parts->get(0);
            $pid = $parts->get(1);
            $percentage = $parts->get(2);
            $memory = $parts->get(3);
            $command = $parts->get(4);

            return Maybe::all($user, $pid, $percentage, $memory, $command)
                ->map(fn(string $user, string $pid, string $percentage, string $memory, string $command) => new Process(
                    new Pid((int) $pid),
                    new User($user),
                    new Percentage((float) $percentage),
                    new Memory((float) $memory),
                    $this->clock->at($start),
                    new Command($command),
                ))
                ->match(
                    static fn($process) => $process,
                    static fn() => throw new \RuntimeException(join(' ', $parts)->toString()),
                );
        });

        return Set::of(...$processes->toList());
    }

    private function format(): string
    {
        return \PHP_OS === 'Linux' ? 'lstart,user,pid,%cpu,%mem,cmd' : 'lstart,user,pid,%cpu,%mem,command';
    }
}
