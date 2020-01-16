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
use Innmind\Url\PathInterface;

interface Server
{
    public function cpu(): Cpu;
    public function memory(): Memory;
    public function processes(): Processes;
    public function loadAverage(): LoadAverage;
    public function disk(): Disk;
    public function tmp(): PathInterface;
}
