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
use Innmind\Immutable\Maybe;

interface Server
{
    /**
     * @return Maybe<Cpu>
     */
    public function cpu(): Maybe;
    public function memory(): Memory;
    public function processes(): Processes;
    public function loadAverage(): LoadAverage;
    public function disk(): Disk;
    public function tmp(): Path;
}
