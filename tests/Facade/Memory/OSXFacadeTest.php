<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\Memory\OSXFacade,
    Server\Memory,
    Exception\MemoryUsageNotAccessible,
};
use PHPUnit\Framework\TestCase;

class OSXFacadeTest extends TestCase
{
    public function testInterface()
    {
        if (\PHP_OS !== 'Darwin') {
            return;
        }

        $facade = new OSXFacade;

        $this->assertInstanceOf(Memory::class, $facade());
    }

    public function testThrowWhenProcessFails()
    {
        if (\PHP_OS === 'Darwin') {
            return;
        }

        $this->expectException(MemoryUsageNotAccessible::class);

        (new OSXFacade)();
    }
}
