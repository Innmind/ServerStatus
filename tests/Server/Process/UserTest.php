<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\User;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testInterface()
    {
        $user = User::of('foo');

        $this->assertSame('foo', $user->toString());
    }
}
