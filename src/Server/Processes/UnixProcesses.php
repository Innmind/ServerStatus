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
    Map,
};
use Symfony\Component\Process\Process as SfProcess;

final class UnixProcesses implements Processes
{
    private Clock $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): Map
    {
        return $this->parse(
            $this->run('ps aux'),
        );
    }

    public function get(Pid $pid): Process
    {
        try {
            $processes = $this->parse(
                $this->run(\sprintf('ps ux -p %s', $pid->toString())),
            );
        } catch (InformationNotAccessible $e) {
            $processes = $this->parse(
                $this->run(\sprintf('ps ux -q %s', $pid->toString())),
            );
        }

        if (!$processes->contains($pid->toInt())) {
            throw new InformationNotAccessible;
        }

        return $processes->get($pid->toInt());
    }

    private function run(string $command): Str
    {
        $process = SfProcess::fromShellCommandline($command);
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
        $columns = $lines
            ->first()
            ->pregSplit('~ +~')
            ->reduce(
                Sequence::strings(),
                static function(Sequence $columns, Str $column): Sequence {
                    return ($columns)($column->toString());
                },
            );

        return $lines
            ->drop(1)
            ->reduce(
                Sequence::of(Sequence::class),
                static function(Sequence $lines, Str $line) use ($columns): Sequence {
                    return ($lines)(
                        $line->pregSplit('~ +~', $columns->size()),
                    );
                },
            )
            ->mapTo(
                Process::class,
                function(Sequence $parts) use ($columns): Process {
                    return new Process(
                        new Pid((int) $parts->get($columns->indexOf('PID'))->toString()),
                        new User($parts->get($columns->indexOf('USER'))->toString()),
                        new Percentage((float) $parts->get($columns->indexOf('%CPU'))->toString()),
                        new Memory((float) $parts->get($columns->indexOf('%MEM'))->toString()),
                        $this->clock->at(
                            $parts->get(
                                $columns->indexOf(PHP_OS === 'Linux' ? 'START' : 'STARTED'),
                            )->toString(),
                        ),
                        new Command($parts->get($columns->indexOf('COMMAND'))->toString()),
                    );
                },
            )
            ->reduce(
                Map::of('int', Process::class),
                static function(Map $processes, Process $process): Map {
                    return ($processes)(
                        $process->pid()->toInt(),
                        $process,
                    );
                },
            );
    }
}
