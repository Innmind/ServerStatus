<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Process;

use Innmind\Server\Status\Server\Process\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testInterface()
    {
        $user = new User('foo');

        $this->assertSame('foo', $user->toString());
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\EmptyUserNotAllowed
     */
    public function testThrowWhenEmptyUser()
    {
        new User('');
    }
}
