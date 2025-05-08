<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Cpu;

use Innmind\Server\Status\Server\Cpu\Cores;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class CoresTest extends TestCase
{
    public function testInterface()
    {
        $cores = Cores::of(8);

        $this->assertSame(8, $cores->toInt());
        $this->assertSame('8', $cores->toString());
    }
}
