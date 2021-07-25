<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Facade\Cpu\OSXFacade,
    Server\Cpu,
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

        $this->assertInstanceOf(Cpu::class, $facade()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenInfoNotAccessible()
    {
        if (\PHP_OS === 'Darwin') {
            $this->markTestSkipped();
        }

        $this->assertNull((new OSXFacade)()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }
}
