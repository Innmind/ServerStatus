<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\Command;
use Innmind\Immutable\RegExp;
use PHPUnit\Framework\TestCase;

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

        $this->assertTrue($command->matches(new RegExp('/^foo/')));
        $this->assertFalse($command->matches(new RegExp('/bar/')));
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\EmptyCommandNotAllowed
     */
    public function testThrowWhenEmptyCommand()
    {
        new Command('');
    }
}
