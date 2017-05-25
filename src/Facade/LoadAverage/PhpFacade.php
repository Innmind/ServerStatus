<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\LoadAverage;

use Innmind\Server\Status\{
    Facade\LoadAverageFacade,
    Server\LoadAverage
};

final class PhpFacade implements LoadAverageFacade
{
    public function __invoke(): LoadAverage
    {
        $load = sys_getloadavg();

        return new LoadAverage(
            $load[0],
            $load[1],
            $load[2]
        );
    }
}
