<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk\Volume;

use Innmind\Server\Status\{
    Server\Disk\Volume\MountPoint,
    Exception\EmptyPathNotAllowed,
};
use PHPUnit\Framework\TestCase;

class MountPointTest extends TestCase
{
    public function testInterface()
    {
        $mountPoint = new MountPoint('foo');

        $this->assertSame('foo', $mountPoint->toString());
    }

    public function testThrowWhenEmptyMountPoint()
    {
        $this->expectException(EmptyPathNotAllowed::class);

        new MountPoint('');
    }
}
