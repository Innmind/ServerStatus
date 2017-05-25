<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\MemoryFacade,
    Server\Memory,
    Server\Memory\Bytes,
    Exception\MemoryUsageNotAccessible
};
use Innmind\Immutable\Str;
use Symfony\Component\Process\Process;

final class LinuxFacade implements MemoryFacade
{
    private $entries = [
        'MemTotal' => 'total',
        'Active' => 'active',
        'Inactive' => 'inactive',
        'MemFree' => 'free',
        'SwapCached' => 'swap',
    ];

    public function __invoke(): Memory
    {
        $process = new Process('cat /proc/meminfo');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new MemoryUsageNotAccessible;
        }

        $amounts = (new Str($process->getOutput()))
            ->trim()
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return $line->matches(
                    '('.implode('|', array_keys($this->entries)).')'
                );
            })
            ->reduce(
                new Map('string', 'int'),
                static function(Map $map, Str $line): Map {
                    $elements = $line->capture('~^(?P<key>\s+): +(?P<value>\d+) kB$~');

                    return $map->put(
                        $this->entries[(string) $elements->get('key')],
                        ((int) (string) $elements->get('value')) * Bytes::BYTES
                    );
                }
            );

        $used = $amounts->get('total') - $amounts->get('free');
        $wired = $used - $amounts->get('active') - $amounts->get('inactive');

        return new Memory(
            new Bytes($amounts->get('total')),
            new Bytes($wired),
            new Bytes($amounts->get('active')),
            new Bytes($amounts->get('free')),
            new Bytes($amounts->get('swap')),
            new Bytes($used)
        );
    }
}
