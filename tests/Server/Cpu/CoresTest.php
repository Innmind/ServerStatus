<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Cpu;

use Innmind\Server\Status\{
    Server\Cpu\Cores,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class CoresTest extends TestCase
{
    public function testInterface()
    {
        $cores = new Cores(8);

        $this->assertSame(8, $cores->toInt());
        $this->assertSame('8', $cores->toString());
    }

    public function testThrowWhenCoresLowerThanOne()
    {
        $this->expectException(DomainException::class);

        new Cores(0);
    }
}
