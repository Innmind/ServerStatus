<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Disk\Volume;

use Innmind\Server\Status\Server\Disk\Volume\MountPoint;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class MountPointTest extends TestCase
{
    public function testInterface()
    {
        $mountPoint = MountPoint::of('foo');

        $this->assertSame('foo', $mountPoint->toString());
    }
}
