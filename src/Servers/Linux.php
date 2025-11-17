<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server\Processes,
    Facade\Cpu\LinuxFacade as CpuFacade,
    Facade\Memory\LinuxFacade as MemoryFacade,
    Facade\LoadAverage\PhpFacade as LoadAverageFacade,
    Server\Disk,
};
use Innmind\Server\Control\Server as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\Immutable\Attempt;

/**
 * @internal
 */
final class Linux implements Implementation
{
    private CpuFacade $cpu;
    private MemoryFacade $memory;
    private Processes\Unix $processes;
    private LoadAverageFacade $loadAverage;
    private Disk\Unix $disk;

    private function __construct(Clock $clock, Control $control)
    {
        $this->cpu = new CpuFacade($control->processes());
        $this->memory = new MemoryFacade($control->processes());
        $this->processes = Processes\Unix::linux($clock, $control->processes());
        $this->loadAverage = new LoadAverageFacade;
        $this->disk = Disk\Unix::of($control->processes());
    }

    /**
     * @internal
     */
    public static function of(Clock $clock, Control $control): self
    {
        return new self($clock, $control);
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
