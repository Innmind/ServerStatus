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
    Maybe,
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
     * @return Maybe<Memory>
     */
    public function __invoke(): Maybe
    {
        $total = $this
            ->run(
                Command::foreground('sysctl')
                    ->withArgument('hw.memsize'),
            )
            ->trim()
            ->capture('~^hw.memsize: (?P<total>\d+)$~')
            ->get('total')
            ->map(static fn($total) => $total->toString());
        $swap = $this
            ->run(
                Command::foreground('sysctl')
                    ->withArgument('vm.swapusage'),
            )
            ->trim()
            ->capture('~used = (?P<swap>\d+[\.,]?\d*[KMGTP])~')
            ->get('swap')
            ->map(static fn($swap) => $swap->toString())
            ->flatMap(static fn($swap) => Bytes::of($swap));
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
            ->flatMap(static fn($unused) => Bytes::of($unused));
        $used = $amounts
            ->get('used')
            ->flatMap(static fn($used) => Bytes::of($used));
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
            ->map(static fn($active) => $active->toString());

        return Maybe::all($total, $active, $unused, $swap, $used)
            ->map(static fn(string $total, string $active, Bytes $unused, Bytes $swap, Bytes $used) => new Memory(
                new Bytes((int) $total),
                new Bytes(((int) $active) * 4096),
                $unused,
                $swap,
                $used,
            ));
    }

    private function run(Command $command): Str
    {
        return $this
            ->processes
            ->execute($command->withEnvironment(
                'PATH',
                $this->path->toString(),
            ))
            ->wait()
            ->match(
                static fn($success) => Str::of($success->output()->toString()),
                static fn() => Str::of(''),
            );
    }
}
