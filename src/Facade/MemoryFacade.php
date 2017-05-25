<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade;

use Innmind\Server\Status\Server\Memory;

interface MemoryFacade
{
    public function __invoke(): Memory;
}
