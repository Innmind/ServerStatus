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
    Clock\PointInTime\Delay,
    Exception\InformationNotAccessible,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\{
    Str,
    Sequence,
    Map,
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

    public function all(): Map
    {
        return $this->parse(
            $this->run('ps -eo '.$this->format()),
        );
    }

    public function get(Pid $pid): Process
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

        if (!$processes->contains($pid->toInt())) {
            throw new InformationNotAccessible;
        }

        return $processes->get($pid->toInt());
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
     * @return Map<int, Process>
     */
    private function parse(Str $output): Map
    {
        $lines = $output
            ->trim()
            ->split("\n");

        /** @var Sequence<Sequence<Str>> */
        $partsByLine = $lines
            ->drop(1) // columns name
            ->reduce(
                Sequence::of(Sequence::class),
                static function(Sequence $lines, Str $line): Sequence {
                    return ($lines)(
                        $line->pregSplit('~ +~', 10), // 6 columns + 4 spaces in the START column
                    );
                },
            );
        $processes = $partsByLine->mapTo(
            Process::class,
            function(Sequence $parts): Process {
                $startParts = $parts
                    ->take(5)
                    ->mapTo('string', static fn(Str $part): string => $part->toString());
                $start = join(' ', $startParts)->toString();
                $parts = $parts->drop(5);
                \var_dump($start);
                return new Process(
                    new Pid((int) $parts->get(1)->toString()),
                    new User($parts->get(0)->toString()),
                    new Percentage((float) $parts->get(2)->toString()),
                    new Memory((float) $parts->get(3)->toString()),
                    new Delay(
                        $this->clock,
                        $start, // see %c for strftime for the format
                    ),
                    new Command($parts->get(4)->toString()),
                );
            },
        );

        /** @var Map<int, Process> */
        return $processes->reduce(
            Map::of('int', Process::class),
            static function(Map $processes, Process $process): Map {
                return ($processes)(
                    $process->pid()->toInt(),
                    $process,
                );
            },
        );
    }

    private function format(): string
    {
        return \PHP_OS === 'Linux' ? 'lstart,user,pid,%cpu,%mem,cmd' : 'lstart,user,pid,%cpu,%mem,command';
    }
}
