<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server\Disk,
    Server\Disk\Volume\MountPoint,
    Server\Disk\Volume\Usage,
    Server\Memory\Bytes,
    Exception\DiskUsageNotAccessible,
};
use Innmind\Immutable\{
    Str,
    Sequence,
    Map,
};
use Symfony\Component\Process\Process;

final class UnixDisk implements Disk
{
    private static array $columns = [
        'Size' => 'size',
        'Used' => 'used',
        'Avail' => 'available',
        'Use%' => 'usage',
        'Capacity' => 'usage',
        'Mounted' => 'mountPoint',
    ];

    /**
     * {@inheritdoc}
     */
    public function volumes(): Map
    {
        return $this->parse(
            $this->run('df -lh'),
        );
    }

    public function get(MountPoint $point): Volume
    {
        return $this
            ->volumes()
            ->get($point->toString());
    }

    private function run(string $command): Str
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new DiskUsageNotAccessible;
        }

        return Str::of($process->getOutput());
    }

    /**
     * @return Map<string, Volume>
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
                    $column = $column->toString();

                    return ($columns)(self::$columns[$column] ?? $column);
                },
            );

        /** @var Sequence<Sequence<Str>> */
        $partsByLine = $lines
            ->drop(1)
            ->reduce(
                Sequence::of(Sequence::class),
                static function(Sequence $lines, Str $line) use ($columns): Sequence {
                    return ($lines)(
                        $line->pregSplit('~ +~', $columns->size()),
                    );
                },
            );
        $volumes = $partsByLine->mapTo(
            Volume::class,
            function(Sequence $parts) use ($columns): Volume {
                return new Volume(
                    new MountPoint(
                        $parts->get($columns->indexOf('mountPoint'))->toString(),
                    ),
                    Bytes::of(
                        $parts->get($columns->indexOf('size'))->toString(),
                    ),
                    Bytes::of(
                        $parts->get($columns->indexOf('available'))->toString(),
                    ),
                    Bytes::of(
                        $parts->get($columns->indexOf('used'))->toString(),
                    ),
                    new Usage(
                        (float) $parts->get($columns->indexOf('usage'))->toString(),
                    ),
                );
            },
        );

        /** @var Map<string, Volume> */
        return $volumes->reduce(
            Map::of('string', Volume::class),
            static function(Map $volumes, Volume $volume): Map {
                return ($volumes)(
                    $volume->mountPoint()->toString(),
                    $volume,
                );
            },
        );
    }
}
