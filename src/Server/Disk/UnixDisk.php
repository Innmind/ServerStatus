<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\{
    Server\Disk,
    Server\Disk\Volume\MountPoint,
    Server\Disk\Volume\Usage,
    Server\Memory\Bytes,
};
use Innmind\Server\Control\Server\{
    Processes,
    Command,
    Process\Output,
};
use Innmind\Immutable\{
    Str,
    Sequence,
    Set,
    Maybe,
};

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

    private Processes $processes;

    public function __construct(Processes $processes)
    {
        $this->processes = $processes;
    }

    public function volumes(): Set
    {
        return $this
            ->processes
            ->execute(
                Command::foreground('df')
                    ->withShortOption('h'),
            )
            ->wait()
            ->maybe()
            ->map(
                static fn($success) => $success
                    ->output()
                    ->filter(static fn($_, $type) => $type === Output\Type::output) // discard errors such as "df: getattrlist failed"
                    ->toString(),
            )
            ->map(Str::of(...))
            ->map($this->parse(...))
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
        $volumes = $partsByLine->map(static function($parts) use ($columns): Maybe {
            $mountPoint = $columns
                ->indexOf('mountPoint')
                ->flatMap(static fn($index) => $parts->get($index));
            $size = $columns
                ->indexOf('size')
                ->flatMap(static fn($index) => $parts->get($index))
                ->flatMap(static fn($size) => Bytes::of($size));
            $available = $columns
                ->indexOf('available')
                ->flatMap(static fn($index) => $parts->get($index))
                ->flatMap(static fn($available) => Bytes::of($available));
            $used = $columns
                ->indexOf('used')
                ->flatMap(static fn($index) => $parts->get($index))
                ->flatMap(static fn($used) => Bytes::of($used));
            $usage = $columns
                ->indexOf('usage')
                ->flatMap(static fn($index) => $parts->get($index));

            return Maybe::all($mountPoint, $size, $available, $used, $usage)
                ->map(static fn(string $mountPoint, Bytes $size, Bytes $available, Bytes $used, string $usage) => new Volume(
                    new MountPoint($mountPoint),
                    $size,
                    $available,
                    $used,
                    new Usage((float) $usage),
                ));
        });

        /** @var Set<Volume> */
        return $volumes->reduce(
            Set::of(),
            static fn(Set $volumes, Maybe $volume) => $volume->match(
                static fn(Volume $volume) => ($volumes)($volume),
                static fn() => $volumes,
            ),
        );
    }
}
