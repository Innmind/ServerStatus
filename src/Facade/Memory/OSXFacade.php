<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Server\Memory,
    Server\Memory\Bytes,
    Exception\MemoryUsageNotAccessible
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
            ->capture('~used = (?P<swap>\d+\.?\d*[KMGTP])~')
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
            new Bytes((int) (string) $total),
            $this->parse($amounts->get('wired')),
            new Bytes(((int) (string) $active) * 4096),
            $this->parse($amounts->get('unused')),
            $this->parse($swap),
            $this->parse($amounts->get('used'))
        );
    }

    private function run(string $command): Str
    {
        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new MemoryUsageNotAccessible;
        }

        return new Str($process->getOutput());
    }

    private function parse(Str $str): Bytes
    {
        switch ($str->substring(-1)) {
            case 'K':
                $multiplier = Bytes::BYTES;
                break;

            case 'M':
                $multiplier = Bytes::KILOBYTES;
                break;

            case 'G':
                $multiplier = Bytes::MEGABYTES;
                break;

            case 'T':
                $multiplier = Bytes::GIGABYTES;
                break;

            case 'P':
                $multiplier = Bytes::TERABYTES;
                break;
        }

        return new Bytes(
            (int) (((float) (string) $str->substring(0, -1)) * $multiplier)
        );
    }
}
