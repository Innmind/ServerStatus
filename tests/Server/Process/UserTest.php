<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\{
    Server\Process\User,
    Exception\EmptyUserNotAllowed,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testInterface()
    {
        $user = new User('foo');

        $this->assertSame('foo', $user->toString());
    }

    public function testThrowWhenEmptyUser()
    {
        $this->expectException(EmptyUserNotAllowed::class);

        new User('');
    }
}
