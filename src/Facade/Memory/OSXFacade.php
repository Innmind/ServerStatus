<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Server\Memory,
    Server\Memory\Bytes,
    EnvironmentPath,
};
use Innmind\Server\Control\Server\{
    Processes,
    Command,
};
use Innmind\Immutable\{
    Str,
    Attempt,
    Maybe,
    Monoid\Concat,
};

/**
 * @internal
 */
final class OSXFacade
{
    private Processes $processes;
    private EnvironmentPath $path;

    public function __construct(Processes $processes, EnvironmentPath $path)
    {
        $this->processes = $processes;
        $this->path = $path;
    }

    /**
     * @return Attempt<Memory>
     */
    public function __invoke(): Attempt
    {
        $total = $this
            ->run(
                Command::foreground('sysctl')
                    ->withArgument('hw.memsize'),
            )
            ->trim()
            ->capture('~^hw.memsize: (?P<total>\d+)$~')
            ->get('total')
            ->map(static fn($total) => $total->toString())
            ->flatMap(Bytes::maybe(...));
        $swap = $this
            ->run(
                Command::foreground('sysctl')
                    ->withArgument('vm.swapusage'),
            )
            ->trim()
            ->capture('~used = (?P<swap>\d+[\.,]?\d*[KMGTP])~')
            ->get('swap')
            ->map(static fn($swap) => $swap->toString())
            ->flatMap(Bytes::maybe(...));
        $amounts = $this
            ->run(
                Command::foreground('top')
                    ->withShortOption('l', '1')
                    ->withShortOption('s', '0')
                    ->pipe(
                        Command::foreground('grep')
                            ->withArgument('PhysMem'),
                    ),
            )
            ->trim()
            ->capture(
                '~^PhysMem: (?P<used>\d+[KMGTP]) used \((?P<wired>\d+[KMGTP]) wired(, \d+[KMGTP] compressor)?\), (?P<unused>\d+[KMGTP]) unused.$~',
            )
            ->map(static fn($_, $amount) => $amount->toString());
        $unused = $amounts
            ->get('unused')
            ->flatMap(Bytes::maybe(...));
        $used = $amounts
            ->get('used')
            ->flatMap(Bytes::maybe(...));
        $active = $this
            ->run(
                Command::foreground('vm_stat')->pipe(
                    Command::foreground('grep')
                        ->withArgument('Pages active'),
                ),
            )
            ->trim()
            ->capture('~(?P<active>\d+)~')
            ->get('active')
            ->map(static fn($active) => $active->toString())
            ->flatMap(Bytes::maybe(...))
            ->map(static fn($bytes) => $bytes->toInt() * 4096)
            ->map(Bytes::of(...));

        return Maybe::all($total, $active, $unused, $swap, $used)
            ->map(static fn(Bytes $total, Bytes $active, Bytes $unused, Bytes $swap, Bytes $used) => new Memory(
                $total,
                $active,
                $unused,
                $swap,
                $used,
            ))
            ->match(
                Attempt::result(...),
                static fn() => Attempt::error(new \RuntimeException('Failed to parse memory usage')),
            );
    }

    private function run(Command $command): Str
    {
        return $this
            ->processes
            ->execute($command->withEnvironment(
                'PATH',
                $this->path->toString(),
            ))
            ->maybe()
            ->flatMap(static fn($process) => $process->wait()->maybe())
            ->toSequence()
            ->flatMap(static fn($success) => $success->output())
            ->map(static fn($chunk) => $chunk->data())
            ->fold(new Concat);
    }
}
