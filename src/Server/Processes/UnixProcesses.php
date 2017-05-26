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
    Exception\InformationNotAccessible
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Immutable\{
    MapInterface,
    Str,
    StreamInterface,
    Sequence,
    Map
};
use Symfony\Component\Process\Process as SfProcess;

final class UnixProcesses implements Processes
{
    private $clock;

    public function __construct(TimeContinuumInterface $clock)
    {
        $this->clock = $clock;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): MapInterface
    {
        return $this->parse(
            $this->run('ps aux')
        );
    }

    public function get(Pid $pid): Process
    {
        try {
            $processes = $this->parse(
                $this->run(sprintf('ps ux -p %s', $pid))
            );
        } catch (InformationNotAccessible $e) {
            $processes = $this->parse(
                $this->run(sprintf('ps ux -q %s', $pid))
            );
        }

        if (!$processes->contains($pid->toInt())) {
            throw new InformationNotAccessible;
        }

        return $processes->get($pid->toInt());
    }

    private function run(string $command): Str
    {
        $process = new SfProcess($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new InformationNotAccessible;
        }

        return new Str($process->getOutput());
    }

    /**
     * @return MapInterface<int, Process>
     */
    private function parse(Str $output): MapInterface
    {
        $lines = $output
            ->trim()
            ->split("\n");
        $columns = $lines
            ->first()
            ->pregSplit('~ +~')
            ->reduce(
                new Sequence,
                static function(Sequence $columns, Str $column): Sequence {
                    return $columns->add((string) $column);
                }
            );

        return $lines
            ->drop(1)
            ->reduce(
                new Sequence,
                static function(Sequence $lines, Str $line) use ($columns): Sequence {
                    return $lines->add(
                        $line->pregSplit('~ +~', $columns->size())
                    );
                }
            )
            ->map(function(StreamInterface $parts) use ($columns): Process {
                return new Process(
                    new Pid((int) (string) $parts->get($columns->indexOf('PID'))),
                    new User((string) $parts->get($columns->indexOf('USER'))),
                    new Percentage((float) (string) $parts->get($columns->indexOf('%CPU'))),
                    new Memory((float) (string) $parts->get($columns->indexOf('%MEM'))),
                    $this->clock->at(
                        (string) $parts->get(
                            $columns->indexOf(PHP_OS === 'Linux' ? 'START' : 'STARTED')
                        )
                    ),
                    new Command((string) $parts->get($columns->indexOf('COMMAND')))
                );
            })
            ->reduce(
                new Map('int', Process::class),
                static function(Map $processes, Process $process): Map {
                    return $processes->put(
                        $process->pid()->toInt(),
                        $process
                    );
                }
            );
    }
}
