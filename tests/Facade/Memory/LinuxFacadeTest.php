<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\Memory\LinuxFacade,
    Server\Memory,
};
use PHPUnit\Framework\TestCase;

class LinuxFacadeTest extends TestCase
{
    public function testInterface()
    {
        if (\PHP_OS !== 'Linux') {
            $this->markTestSkipped();
        }

        $facade = new LinuxFacade;

        $this->assertInstanceOf(Memory::class, $facade()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }

    public function testReturnNohingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Linux') {
            $this->markTestSkipped();
        }

        $this->assertNull((new LinuxFacade)()->match(
            static fn($memory) => $memory,
            static fn() => null,
        ));
    }
}
