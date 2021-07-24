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
                Map::of('string', 'int'),
                static function(Map $map, Str $line): Map {
                    $elements = $line->capture('~^(?P<key>[a-zA-Z]+): +(?P<value>\d+) kB$~');

                    return ($map)(
                        self::$entries[$elements->get('key')->toString()],
                        ((int) $elements->get('value')->toString()) * 1024, // 1024 represents a kilobyte
                    );
                },
            );

        $used = $amounts->get('total') - $amounts->get('free');
        $wired = $used - $amounts->get('active') - $amounts->get('inactive');

        return new Memory(
            new Bytes($amounts->get('total')),
            new Bytes($wired),
            new Bytes($amounts->get('active')),
            new Bytes($amounts->get('free')),
            new Bytes($amounts->get('swap')),
            new Bytes($used),
        );
    }
}
