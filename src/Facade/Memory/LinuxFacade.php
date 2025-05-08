<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Server\Memory,
    Server\Memory\Bytes,
};
use Innmind\Server\Control\Server\{
    Processes,
    Command,
};
use Innmind\Validation\Is;
use Innmind\Immutable\{
    Str,
    Map,
    Attempt,
    Maybe,
    Monoid\Concat,
};

/**
 * @internal
 */
final class LinuxFacade
{
    private static array $entries = [
        'MemTotal' => 'total',
        'Active' => 'active',
        'MemFree' => 'free',
        'SwapCached' => 'swap',
    ];

    private Processes $processes;

    public function __construct(Processes $processes)
    {
        $this->processes = $processes;
    }

    /**
     * @return Attempt<Memory>
     */
    public function __invoke(): Attempt
    {
        return $this
            ->processes
            ->execute(
                Command::foreground('cat')
                    ->withArgument('/proc/meminfo'),
            )
            ->flatMap(static fn($process) => $process->wait()->match(
                Attempt::result(...),
                static fn() => Attempt::error(new \RuntimeException('Failed to retrieve memory usage')),
            ))
            ->map(
                static fn($success) => $success
                    ->output()
                    ->map(static fn($chunk) => $chunk->data())
                    ->fold(new Concat),
            )
            ->flatMap($this->parse(...));
    }

    /**
     * @return Attempt<Memory>
     */
    private function parse(Str $output): Attempt
    {
        $amounts = $output
            ->trim()
            ->split("\n")
            ->filter(static fn(Str $line) => $line->matches(
                '~^('.\implode('|', \array_keys(self::$entries)).'):~',
            ))
            ->map(
                static fn($line) => $line
                    ->capture('~^(?P<key>[a-zA-Z]+): +(?P<value>\d+) kB$~')
                    ->map(static fn($_, $part) => $part->toString()),
            )
            ->flatMap(
                static fn($elements) => Maybe::all(
                    $elements->get('key'),
                    $elements
                        ->get('value')
                        ->map(static fn($value) => (int) $value)
                        ->keep(
                            Is::int()
                                ->positive()
                                ->or(Is::value(0))
                                ->asPredicate(),
                        )
                        ->map(Bytes::of(...)),
                )
                    ->map(static fn(string $key, Bytes $value) => [$key, $value])
                    ->toSequence(),
            );
        dump($amounts);
        $amounts = Map::of(...$amounts->toList())
            ->map(static fn($_, $value) => Bytes::of(
                $value->toInt() * 1024, // 1024 represents a kilobyte
            ));
        $total = $amounts->get('total');
        $free = $amounts->get('free');
        $active = $amounts->get('active');
        $swap = $amounts->get('swap');
        $used = Maybe::all($total, $free)
            ->map(static fn(Bytes $total, Bytes $free) => $total->toInt() - $free->toInt())
            ->keep(
                Is::int()
                    ->positive()
                    ->or(Is::value(0))
                    ->asPredicate(),
            )
            ->map(Bytes::of(...));

        return Maybe::all($total, $active, $free, $swap, $used)
            ->map(Memory::of(...))
            ->match(
                Attempt::result(...),
                static fn() => Attempt::error(new \RuntimeException('Failed to parse memory usage')),
            );
    }
}
