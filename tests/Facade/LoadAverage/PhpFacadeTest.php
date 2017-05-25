<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\LoadAverage;

use Innmind\Server\Status\{
    Facade\LoadAverage\PhpFacade,
    Facade\LoadAverageFacade,
    Server\LoadAverage
};
use PHPUnit\Framework\TestCase;

class PhpFacadeTest extends TestCase
{
    public function testInterface()
    {
        $facade = new PhpFacade;

        $this->assertInstanceOf(LoadAverageFacade::class, $facade);
        $this->assertInstanceOf(LoadAverage::class, $facade());
    }
}
