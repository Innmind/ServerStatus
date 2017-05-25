<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Memory;

use Innmind\Server\Status\Server\Memory\Bytes;
use PHPUnit\Framework\TestCase;

class BytesTest extends TestCase
{
    /**
     * @dataProvider steps
     */
    public function testInterface($value, $expected)
    {
        $bytes = new Bytes($value);

        $this->assertSame($value, $bytes->toInt());
        $this->assertSame($expected, (string) $bytes);
    }

    /**
     * @expectedException Innmind\Server\Status\Exception\BytesCannotBeNegative
     */
    public function testThrowWhenNegative()
    {
        new Bytes(-1);
    }

    public function steps(): array
    {
        return [
            [512, '512B'],
            [1023, '1023B'],
            [1024, '1KB'],
            [(1024 ** 2)-1, '1023.999KB'],
            [1024 ** 2, '1MB'],
            [(1024 ** 3)-1, '1024MB'],
            [1024 ** 3, '1GB'],
            [(1024 ** 4)-1, '1024GB'],
            [1024 ** 4, '1TB'],
            [(1024 ** 5)-1, '1024TB'],
            [1024 ** 5, '1PB'],
            [(1024 ** 6)-1, '1024PB'],
        ];
    }
}