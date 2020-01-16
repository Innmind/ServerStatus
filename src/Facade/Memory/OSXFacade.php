<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Server\Memory,
    Server\Memory\Bytes,
    Exception\MemoryUsageNotAccessible,
};
use Innmind\Immutable\Str;
use Symfony\Component\Process\Process;

final class OSXFacade
{
    public function __invoke(): Memory
    {
        $total = $this
            ->run('sysctl hw.memsize')
            ->trim()
            ->capture('~^hw.memsize: (?P<total>\d+)$~')
            ->get('total');
        $swap = $this
            ->run('sysctl vm.swapusage')
            ->trim()
            ->capture('~used = (?P<swap>\d+[\.,]?\d*[KMGTP])~')
            ->get('swap');
        $amounts = $this
            ->run('top -l 1 -s 0 | grep PhysMem')
            ->trim()
            ->capture(
                '~^PhysMem: (?P<used>\d+[KMGTP]) used \((?P<wired>\d+[KMGTP]) wired\), (?P<unused>\d+[KMGTP]) unused.$~'
            );
        $active = $this
            ->run('vm_stat | grep \'Pages active\'')
            ->trim()
            ->capture('~(?P<active>\d+)~')
            ->get('active');

        return new Memory(
            new Bytes((int) $total->toString()),
            Bytes::of($amounts->get('wired')->toString()),
            new Bytes(((int) $active->toString()) * 4096),
            Bytes::of($amounts->get('unused')->toString()),
            Bytes::of($swap->toString()),
            Bytes::of($amounts->get('used')->toString()),
        );
    }

    private function run(string $command): Str
    {
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new MemoryUsageNotAccessible;
        }

        return Str::of($process->getOutput());
    }
}
