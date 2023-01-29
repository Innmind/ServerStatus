<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server,
    Server\Processes,
    Server\LoadAverage,
    Facade\Cpu\OSXFacade as CpuFacade,
    Facade\Memory\OSXFacade as MemoryFacade,
    Facade\LoadAverage\PhpFacade as LoadAverageFacade,
    Server\Processes\UnixProcesses,
    Server\Disk,
    Server\Disk\UnixDisk,
    EnvironmentPath,
};
use Innmind\Server\Control\Server as Control;
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

    public function __construct(Clock $clock, Control $control, EnvironmentPath $path)
    {
        $this->cpu = new CpuFacade($control->processes());
        $this->memory = new MemoryFacade($control->processes(), $path);
        $this->processes = new UnixProcesses($clock, $control->processes());
        $this->loadAverage = new LoadAverageFacade;
        $this->disk = new UnixDisk($control->processes());
    }

    public function cpu(): Maybe
    {
        return ($this->cpu)();
    }

    public function memory(): Maybe
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
