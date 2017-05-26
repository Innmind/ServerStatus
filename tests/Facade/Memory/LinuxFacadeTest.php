<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Memory;

use Innmind\Server\Status\{
    Facade\Memory\LinuxFacade,
    Server\Memory,
    Exception\MemoryUsageNotAccessible
};
use PHPUnit\Framework\TestCase;

class LinuxFacadeTest extends TestCase
{
    public function testInterface()
    {
        if (PHP_OS !== 'Linux') {
            return;
        }

        $facade = new LinuxFacade;

        $this->assertInstanceOf(Memory::class, $facade());
    }

    public function testThrowWhenProcessFails()
    {
        if (PHP_OS === 'Linux') {
            return;
        }

        $this->expectException(MemoryUsageNotAccessible::class);

        (new LinuxFacade)();
    }
}
