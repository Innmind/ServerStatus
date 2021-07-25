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
    Maybe,
};
use function Innmind\Immutable\join;
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

    public function volumes(): Map
    {
        return $this->parse(
            $this->run('df -lh'),
        );
    }

    public function get(MountPoint $point): Maybe
    {
        return $this->volumes()->get($point->toString());
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
            ->map(static fn($line) => $line->pregSplit('~ +~'))
            ->map(static fn($parts) => $parts->reduce(
                Sequence::strings(),
                static function(Sequence $columns, Str $column): Sequence {
                    $column = $column->toString();

                    return ($columns)(self::$columns[$column] ?? $column);
                },
            ))
            ->match(
                static fn($columns) => $columns,
                static fn() => Sequence::strings(),
            );

        /** @var Sequence<Sequence<string>> */
        $partsByLine = $lines
            ->drop(1)
            ->reduce(
                Sequence::of(),
                static function(Sequence $lines, Str $line) use ($columns): Sequence {
                    return ($lines)(
                        $line
                            ->pregSplit('~ +~', $columns->size())
                            ->map(static fn($part) => $part->toString()),
                    );
                },
            );
        $volumes = $partsByLine->map(static function(Sequence $parts) use ($columns): Volume {
            $mountPoint = $columns
                ->indexOf('mountPoint')
                ->flatMap(static fn($index) => $parts->get($index));
            $size = $columns
                ->indexOf('size')
                ->flatMap(static fn($index) => $parts->get($index));
            $available = $columns
                ->indexOf('available')
                ->flatMap(static fn($index) => $parts->get($index));
            $used = $columns
                ->indexOf('used')
                ->flatMap(static fn($index) => $parts->get($index));
            $usage = $columns
                ->indexOf('usage')
                ->flatMap(static fn($index) => $parts->get($index));

            return Maybe::all($mountPoint, $size, $available, $used, $usage)
                ->map(static fn(string $mountPoint, string $size, string $available, string $used, string $usage) => new Volume(
                    new MountPoint($mountPoint),
                    Bytes::of($size),
                    Bytes::of($available),
                    Bytes::of($used),
                    new Usage((float) $usage),
                ))
                ->match(
                    static fn($volume) => $volume,
                    static fn() => throw new \RuntimeException(join(' ', $parts)->toString()),
                );
        });

        /** @var Map<string, Volume> */
        return $volumes->reduce(
            Map::of(),
            static function(Map $volumes, Volume $volume): Map {
                return ($volumes)(
                    $volume->mountPoint()->toString(),
                    $volume,
                );
            },
        );
    }
}
