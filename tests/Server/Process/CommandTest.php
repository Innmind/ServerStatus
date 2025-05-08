<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\Command;
use Innmind\Immutable\RegExp;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testInterface()
    {
        $command = Command::of('foo');

        $this->assertSame('foo', $command->toString());
    }

    public function testMatches()
    {
        $command = Command::of('foo');

        $this->assertTrue($command->matches(RegExp::of('/^foo/')));
        $this->assertFalse($command->matches(RegExp::of('/bar/')));
    }
}
