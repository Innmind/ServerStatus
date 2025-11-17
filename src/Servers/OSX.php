<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server\Processes,
    Facade\Cpu\OSXFacade as CpuFacade,
    Facade\Memory\OSXFacade as MemoryFacade,
    Facade\LoadAverage\PhpFacade as LoadAverageFacade,
    Server\Disk,
    EnvironmentPath,
};
use Innmind\Server\Control\Server as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\Attempt;

/**
 * @internal
 */
final class OSX implements Implementation
{
    private CpuFacade $cpu;
    private MemoryFacade $memory;
    private Processes\Unix $processes;
    private LoadAverageFacade $loadAverage;
    private Disk $disk;

    private function __construct(Clock $clock, Control $control, EnvironmentPath $path)
    {
        $this->cpu = new CpuFacade($control->processes());
        $this->memory = new MemoryFacade($control->processes(), $path);
        $this->processes = Processes\Unix::osx($clock, $control->processes());
        $this->loadAverage = new LoadAverageFacade;
        $this->disk = Disk::of($control->processes());
    }

    /**
     * @internal
     */
    public static function of(Clock $clock, Control $control, EnvironmentPath $path): self
    {
        return new self($clock, $control, $path);
    }

    #[\Override]
    public function cpu(): Attempt
    {
        return ($this->cpu)();
    }

    #[\Override]
    public function memory(): Attempt
    {
        return ($this->memory)();
    }

    #[\Override]
    public function processes(): Processes
    {
        return $this->processes;
    }

    #[\Override]
    public function loadAverage(): Attempt
    {
        return ($this->loadAverage)();
    }

    #[\Override]
    public function disk(): Disk
    {
        return $this->disk;
    }
}
