<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Servers;

use Innmind\Server\Status\{
    Server\Cpu,
    Server\Memory,
    Server\Processes,
    Server\LoadAverage,
    Server\Disk,
};
use Innmind\Url\Path;
use Innmind\Immutable\Attempt;

/**
 * @internal
 */
interface Implementation
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
