<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade;

use Innmind\Server\Status\Server\LoadAverage;

interface LoadAverageFacade
{
    public function __invoke(): LoadAverage;
}
