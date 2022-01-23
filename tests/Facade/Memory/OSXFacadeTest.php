<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\Memory\OSXFacade,
    Server\Memory,
};
use PHPUnit\Framework\TestCase;

class OSXFacadeTest extends TestCase
{
    public function testInterface()
    {
        if (\PHP_OS !== 'Darwin') {
            $this->markTestSkipped();
        }

        $facade = new OSXFacade;

        $this->assertInstanceOf(Memory::class, $facade()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Darwin') {
            $this->markTestSkipped();
        }

        $this->assertNull((new OSXFacade)()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }
}
