<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Facade\LoadAverage;

use Innmind\Server\Status\Server\LoadAverage;
use Innmind\Immutable\Attempt;

/**
 * @internal
 */
final class PhpFacade
{
    /**
     * @return Attempt<LoadAverage>
     */
    public function __invoke(): Attempt
    {
        /** @var array{0: float, 1: float, 2: float} */
        $load = \sys_getloadavg();

        return LoadAverage::maybe($load[0], $load[1], $load[2])->match(
            Attempt::result(...),
            static fn() => Attempt::error(new \RuntimeException('Failed to load load average')),
        );
    }
}
