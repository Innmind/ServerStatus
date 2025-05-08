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
use Innmind\Validation\Is;
use Innmind\Immutable\{
    Str,
    Set,
    Maybe,
    Map,
    Monoid\Concat,
};

final class Unix implements Disk
{
    private static array $columns = [
        'Size' => 'size',
        'Used' => 'used',
        'Avail' => 'available',
        'Use%' => 'usage',
        'Capacity' => 'usage',
        'Mounted' => 'mountPoint',
    ];

    private function __construct(
        private Processes $processes,
    ) {
    }

    /**
     * @internal
     */
    public static function of(Processes $processes): self
    {
        return new self($processes);
    }

    #[\Override]
    public function volumes(): Set
    {
        return $this
            ->processes
            ->execute(
                Command::foreground('df')
                    ->withShortOption('h'),
            )
            ->maybe()
            ->flatMap(static fn($process) => $process->wait()->maybe())
            ->map(
                static fn($success) => $success
                    ->output()
                    ->filter(static fn($chunk) => $chunk->type() === Output\Type::output) // discard errors such as "df: getattrlist failed"
                    ->map(static fn($chunk) => $chunk->data())
                    ->fold(new Concat),
            )
            ->map($this->parse(...))
            ->toSequence()
            ->toSet()
            ->flatMap(static fn($volumes) => $volumes);
    }

    #[\Override]
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
            ->toSequence()
            ->flatMap(static fn($line) => $line->pregSplit('~ +~'))
            ->map(static fn($column) => $column->toString())
            ->map(static fn($column) => self::$columns[$column] ?? $column);

        $partsByLine = $lines
            ->drop(1)
            ->map(
                static fn($line) => $line
                    ->pregSplit('~ +~', $columns->size())
                    ->map(static fn($column) => $column->toString()),
            )
            ->map(static fn($parts) => $columns->zip($parts))
            ->map(static fn($parts) => Map::of(...$parts->toList()));
        $volumes = $partsByLine->map(static function($parts): Maybe {
            $mountPoint = $parts
                ->get('mountPoint')
                ->keep(Is::string()->nonEmpty()->asPredicate())
                ->map(MountPoint::of(...));
            $size = $parts
                ->get('size')
                ->flatMap(Bytes::maybe(...));
            $available = $parts
                ->get('available')
                ->flatMap(Bytes::maybe(...));
            $used = $parts
                ->get('used')
                ->flatMap(Bytes::maybe(...));
            $usage = $parts
                ->get('usage')
                ->map(static fn($value) => (float) $value)
                ->flatMap(Usage::maybe(...));

            return Maybe::all($mountPoint, $size, $available, $used, $usage)
                ->map(Volume::of(...));
        });

        return $volumes
            ->flatMap(static fn($volume) => $volume->toSequence()) // discard unparsed volumes
            ->toSet();
    }
}
