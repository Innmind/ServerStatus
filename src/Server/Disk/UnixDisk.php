<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server\Disk,
    Server\Disk\Volume\MountPoint,
    Server\Disk\Volume\Usage,
    Server\Memory\Bytes,
    Exception\RuntimeException,
};
use Innmind\Immutable\{
    Str,
    Sequence,
    Set,
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

    public function volumes(): Set
    {
        return $this
            ->run('df -lh')
            ->map(fn($output) => $this->parse($output))
            ->match(
                static fn($volumes) => $volumes,
                static fn() => Set::of(),
            );
    }

    public function get(MountPoint $point): Maybe
    {
        return $this
            ->volumes()
            ->find(static fn($volume) => $volume->mountPoint()->equals($point));
    }

    /**
     * @return Maybe<Str>
     */
    private function run(string $command): Maybe
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            /** @var Maybe<Str> */
            return Maybe::nothing();
        }

        return Maybe::just(Str::of($process->getOutput()));
    }

    /**
     * @return Set<Volume>
     */
    private function parse(Str $output): Set
    {
        $lines = $output
            ->trim()
            ->split("\n");
        $columns = $lines
            ->first()
            ->map(static fn($line) => $line->pregSplit('~ +~'))
            ->map(static fn($columns) => $columns->map(
                static fn($column) => $column->toString(),
            ))
            ->match(
                static fn($columns) => $columns,
                static fn() => Sequence::strings(),
            )
            ->map(static fn($column) => self::$columns[$column] ?? $column);

        $partsByLine = $lines
            ->drop(1)
            ->map(
                static fn($line) => $line
                    ->pregSplit('~ +~', $columns->size())
                    ->map(static fn($column) => $column->toString()),
            );
        $volumes = $partsByLine->map(static function($parts) use ($columns): Volume {
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
                    static fn() => throw new RuntimeException(join(' ', $parts)->toString()),
                );
        });

        return Set::of(...$volumes->toList());
    }
}
