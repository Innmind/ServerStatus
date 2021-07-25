<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server,
    Server\Memory,
    Server\Processes,
    Server\LoadAverage,
    Facade\Cpu\OSXFacade as CpuFacade,
    Facade\Memory\OSXFacade as MemoryFacade,
    Facade\LoadAverage\PhpFacade as LoadAverageFacade,
    Server\Processes\UnixProcesses,
    Server\Disk,
    Server\Disk\UnixDisk,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Url\Path;
use Innmind\Immutable\Maybe;

final class OSX implements Server
{
    private CpuFacade $cpu;
    private MemoryFacade $memory;
    private UnixProcesses $processes;
    private LoadAverageFacade $loadAverage;
    private UnixDisk $disk;

    public function __construct(Clock $clock)
    {
        $this->cpu = new CpuFacade;
        $this->memory = new MemoryFacade;
        $this->processes = new UnixProcesses($clock);
        $this->loadAverage = new LoadAverageFacade;
        $this->disk = new UnixDisk;
    }

    public function cpu(): Maybe
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

    public function tmp(): Path
    {
        return Path::of(\rtrim(\sys_get_temp_dir(), '/').'/');
    }
}
