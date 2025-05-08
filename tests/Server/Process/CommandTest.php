<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\{
    Server\Process\Command,
    Exception\EmptyCommandNotAllowed,
};
use Innmind\Immutable\RegExp;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testInterface()
    {
        $command = new Command('foo');

        $this->assertSame('foo', $command->toString());
    }

    public function testMatches()
    {
        $command = new Command('foo');

        $this->assertTrue($command->matches(RegExp::of('/^foo/')));
        $this->assertFalse($command->matches(RegExp::of('/bar/')));
    }

    public function testThrowWhenEmptyCommand()
    {
        $this->expectException(EmptyCommandNotAllowed::class);

        new Command('');
    }
}
