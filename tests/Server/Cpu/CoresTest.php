<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Cpu;

use Innmind\Server\Status\Server\Cpu\Cores;
use PHPUnit\Framework\TestCase;

class CoresTest extends TestCase
{
    public function testInterface()
    {
        $cores = new Cores(8);

        $this->assertSame(8, $cores->toInt());
        $this->assertSame('8', $cores->toString());
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\DomainException
     */
    public function testThrowWhenCoresLowerThanOne()
    {
        new Cores(0);
    }
}
