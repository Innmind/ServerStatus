<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Facade\Cpu;

use Innmind\Server\Status\{
    Facade\Cpu\OSXFacade,
    Server\Cpu,
};
use Innmind\Server\Control\ServerFactory as Control;
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\TimeWarp\Halt\Usleep;
use Innmind\Stream\Streams;
use PHPUnit\Framework\TestCase;

class OSXFacadeTest extends TestCase
{
    private $server;

    public function setUp(): void
    {
        $this->server = Control::build(
            new Clock,
            Streams::fromAmbientAuthority(),
            new Usleep,
        );
    }

    public function testInterface()
    {
        if (\PHP_OS !== 'Darwin') {
            $this->markTestSkipped();
        }

        $facade = new OSXFacade($this->server->processes());

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

        $this->assertNull((new OSXFacade($this->server->processes()))()->match(
            static fn($cpu) => $cpu,
            static fn() => null,
        ));
    }
}
