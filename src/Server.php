<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

use Innmind\Server\Status\Server\{
    Cpu,
    Memory,
    Processes,
    LoadAverage,
    Disk,
};
use Innmind\Url\Path;
use Innmind\Immutable\Attempt;

interface Server
{
    /**
     * @return Attempt<Cpu>
     */
    public function cpu(): Attempt;

    /**
     * @return Attempt<Memory>
     */
    public function memory(): Attempt;
    public function processes(): Processes;

    /**
     * @return Attempt<LoadAverage>
     */
    public function loadAverage(): Attempt;
    public function disk(): Disk;
    public function tmp(): Path;
}
