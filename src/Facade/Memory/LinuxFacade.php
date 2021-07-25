<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Server\Memory,
    Server\Memory\Bytes,
    Exception\MemoryUsageNotAccessible,
};
use Innmind\Immutable\{
    Str,
    Map,
    Maybe,
};
use Symfony\Component\Process\Process;

final class LinuxFacade
{
    private static array $entries = [
        'MemTotal' => 'total',
        'Active' => 'active',
        'Inactive' => 'inactive',
        'MemFree' => 'free',
        'SwapCached' => 'swap',
    ];

    public function __invoke(): Memory
    {
        $process = Process::fromShellCommandline('cat /proc/meminfo');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new MemoryUsageNotAccessible;
        }

        /** @var Map<string, int> */
        $amounts = Str::of($process->getOutput())
            ->trim()
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return $line->matches(
                    '~^('.\implode('|', \array_keys(self::$entries)).'):~'
                );
            })
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
        $inactive = $amounts->get('inactive');
        $swap = $amounts->get('swap');
        $used = Maybe::all($total, $free)->map(static fn(int $total, int $free) => $total - $free);
        $wired = Maybe::all($active, $inactive)->map(static fn(int $active, int $inactive) => $active - $inactive);

        return Maybe::all($total, $wired, $active, $free, $swap, $used)
            ->map(static fn(int $total, int $wired, int $active, int $free, int $swap, int $used) => new Memory(
                new Bytes($total),
                new Bytes($wired),
                new Bytes($active),
                new Bytes($free),
                new Bytes($swap),
                new Bytes($used),
            ))
            ->match(
                static fn($memory) => $memory,
                static fn() => throw new MemoryUsageNotAccessible,
            );
    }
}
