<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade;

use Innmind\Server\Status\Server\Cpu;

interface CpuFacade
{
    public function __invoke(): Cpu;
}
