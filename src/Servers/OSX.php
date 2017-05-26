<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server,
    Server\Cpu,
    Server\Memory,
    Server\Processes,
    Server\LoadAverage,
    Facade\Cpu\OSXFacade as CpuFacade,
    Facade\Memory\OSXFacade as MemoryFacade,
    Facade\LoadAverage\PhpFacade as LoadAverageFacade,
    Server\Processes\UnixProcesses,
    Server\Disk,
    Server\Disk\UnixDisk
};
use Innmind\TimeContinuum\TimeContinuumInterface;

final class OSX implements Server
{
    private $cpu;
    private $memory;
    private $processes;
    private $loadAverage;
    private $disk;

    public function __construct(TimeContinuumInterface $clock)
    {
        $this->cpu = new CpuFacade;
        $this->memory = new MemoryFacade;
        $this->processes = new UnixProcesses($clock);
        $this->loadAverage = new LoadAverageFacade;
        $this->disk = new UnixDisk;
    }

    public function cpu(): Cpu
    {
        return ($this->cpu)();
    }

    public function memory(): Memory
    {
        return ($this->memory)();
    }
    public function processes(): Processes
    {
        return $this->processes;
    }

    public function loadAverage(): LoadAverage
    {
        return ($this->loadAverage)();
    }

    public function disk(): Disk
    {
        return $this->disk;
    }
}
