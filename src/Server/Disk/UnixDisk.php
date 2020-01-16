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
    MapInterface,
    Str,
    StreamInterface,
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
    public function volumes(): MapInterface
    {
        return $this->parse(
            $this->run('df -lh'),
        );
    }

    public function get(MountPoint $point): Volume
    {
        return $this
            ->volumes()
            ->get((string) $point);
    }

    private function run(string $command): Str
    {
        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new DiskUsageNotAccessible;
        }

        return new Str($process->getOutput());
    }

    /**
     * @return MapInterface<string, Volume>
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
                    $column = (string) $column;

                    return $columns->add(self::$columns[$column] ?? $column);
                },
            );

        return $lines
            ->drop(1)
            ->reduce(
                new Sequence,
                static function(Sequence $lines, Str $line) use ($columns): Sequence {
                    return $lines->add(
                        $line->pregSplit('~ +~', $columns->size())
                    );
                },
            )
            ->map(function(StreamInterface $parts) use ($columns): Volume {
                return new Volume(
                    new MountPoint(
                        (string) $parts->get($columns->indexOf('mountPoint')),
                    ),
                    Bytes::of(
                        (string) $parts->get($columns->indexOf('size')),
                    ),
                    Bytes::of(
                        (string) $parts->get($columns->indexOf('available')),
                    ),
                    Bytes::of(
                        (string) $parts->get($columns->indexOf('used')),
                    ),
                    new Usage(
                        (float) (string) $parts->get($columns->indexOf('usage')),
                    ),
                );
            })
            ->reduce(
                new Map('string', Volume::class),
                static function(Map $volumes, Volume $volume): Map {
                    return $volumes->put(
                        (string) $volume->mountPoint(),
                        $volume,
                    );
                },
            );
    }
}
