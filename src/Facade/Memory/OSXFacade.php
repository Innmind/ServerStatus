<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Server\Memory,
    Server\Memory\Bytes,
};
use Innmind\Immutable\{
    Str,
    Maybe,
};
use Symfony\Component\Process\Process;

final class OSXFacade
{
    /**
     * @return Maybe<Memory>
     */
    public function __invoke(): Maybe
    {
        $total = $this
            ->run('sysctl hw.memsize')
            ->trim()
            ->capture('~^hw.memsize: (?P<total>\d+)$~')
            ->get('total')
            ->map(static fn($total) => $total->toString());
        $swap = $this
            ->run('sysctl vm.swapusage')
            ->trim()
            ->capture('~used = (?P<swap>\d+[\.,]?\d*[KMGTP])~')
            ->get('swap')
            ->map(static fn($swap) => $swap->toString());
        $amounts = $this
            ->run('top -l 1 -s 0 | grep PhysMem')
            ->trim()
            ->capture(
                '~^PhysMem: (?P<used>\d+[KMGTP]) used \((?P<wired>\d+[KMGTP]) wired\), (?P<unused>\d+[KMGTP]) unused.$~'
            )
            ->map(static fn($_, $amount) => $amount->toString());
        $wired = $amounts->get('wired');
        $unused = $amounts->get('unused');
        $used = $amounts->get('used');
        $active = $this
            ->run('vm_stat | grep \'Pages active\'')
            ->trim()
            ->capture('~(?P<active>\d+)~')
            ->get('active')
            ->map(static fn($active) => $active->toString());

        return Maybe::all($total, $wired, $active, $unused, $swap, $used)
            ->map(static fn(string $total, string $wired, string $active, string $unused, string $swap, string $used) => new Memory(
                new Bytes((int) $total),
                Bytes::of($wired),
                new Bytes(((int) $active) * 4096),
                Bytes::of($unused),
                Bytes::of($swap),
                Bytes::of($used),
            ));
    }

    private function run(string $command): Str
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            return Str::of('');
        }

        return Str::of($process->getOutput());
    }
}
