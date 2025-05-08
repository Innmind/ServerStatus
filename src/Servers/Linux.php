<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server,
    Server\Processes,
    Server\LoadAverage,
    Facade\Cpu\LinuxFacade as CpuFacade,
    Facade\Memory\LinuxFacade as MemoryFacade,
    Facade\LoadAverage\PhpFacade as LoadAverageFacade,
    Server\Processes\UnixProcesses,
    Server\Disk,
    Server\Disk\UnixDisk,
};
use Innmind\Server\Control\Server as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\Url\Path;
use Innmind\Immutable\Attempt;
use Innmind\Immutable\Maybe;

final class Linux implements Server
{
    private CpuFacade $cpu;
    private MemoryFacade $memory;
    private UnixProcesses $processes;
    private LoadAverageFacade $loadAverage;
    private UnixDisk $disk;

    public function __construct(Clock $clock, Control $control)
    {
        $this->cpu = new CpuFacade($control->processes());
        $this->memory = new MemoryFacade($control->processes());
        $this->processes = new UnixProcesses($clock, $control->processes());
        $this->loadAverage = new LoadAverageFacade;
        $this->disk = new UnixDisk($control->processes());
    }

    #[\Override]
    public function cpu(): Attempt
    {
        return ($this->cpu)();
    }

    #[\Override]
    public function memory(): Maybe
    {
        return ($this->memory)();
    }

    #[\Override]
    public function processes(): Processes
    {
        return $this->processes;
    }

    #[\Override]
    public function loadAverage(): LoadAverage
    {
        return ($this->loadAverage)();
    }

    #[\Override]
    public function disk(): Disk
    {
        return $this->disk;
    }

    #[\Override]
    public function tmp(): Path
    {
        return Path::of(\rtrim(\sys_get_temp_dir(), '/').'/');
    }
}
