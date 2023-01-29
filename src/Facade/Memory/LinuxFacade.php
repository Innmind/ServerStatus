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
use Innmind\Immutable\{
    Str,
    Map,
    Maybe,
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
     * @return Maybe<Memory>
     */
    public function __invoke(): Maybe
    {
        return $this
            ->processes
            ->execute(
                Command::foreground('cat')
                    ->withArgument('/proc/meminfo'),
            )
            ->wait()
            ->maybe()
            ->map(static fn($success) => $success->output()->toString())
            ->map(Str::of(...))
            ->flatMap($this->parse(...));
    }

    /**
     * @return Maybe<Memory>
     */
    private function parse(Str $output): Maybe
    {
        /** @var Map<string, int> */
        $amounts = $output
            ->trim()
            ->split("\n")
            ->filter(static fn(Str $line) => $line->matches(
                '~^('.\implode('|', \array_keys(self::$entries)).'):~',
            ))
            ->reduce(
                Map::of(),
                static function(Map $map, Str $line): Map {
                    $elements = $line
                        ->capture('~^(?P<key>[a-zA-Z]+): +(?P<value>\d+) kB$~')
                        ->map(static fn($_, $part) => $part->toString());

                    return Maybe::all($elements->get('key'), $elements->get('value'))
                        ->map(static fn(string $key, string $value) => [$key, (int) $value])
                        ->match(
                            static fn($pair) => ($map)(
                                self::$entries[$pair[0]],
                                $pair[1] * 1024, // 1024 represents a kilobyte
                            ),
                            static fn() => $map,
                        );
                },
            );
        $total = $amounts->get('total');
        $free = $amounts->get('free');
        $active = $amounts->get('active');
        $swap = $amounts->get('swap');
        $used = Maybe::all($total, $free)->map(static fn(int $total, int $free) => $total - $free);

        return Maybe::all($total, $active, $free, $swap, $used)
            ->map(static fn(int $total, int $active, int $free, int $swap, int $used) => new Memory(
                new Bytes($total),
                new Bytes($active),
                new Bytes($free),
                new Bytes($swap),
                new Bytes($used),
            ));
    }
}
