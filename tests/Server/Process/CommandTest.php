<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\Command;
use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
{
    public function testInterface()
    {
        $command = new Command('foo');

        $this->assertSame('foo', (string) $command);
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\EmptyCommandNotAllowed
     */
    public function testThrowWhenEmptyCommand()
    {
        new Command('');
    }
}
