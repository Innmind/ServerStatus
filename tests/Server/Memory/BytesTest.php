<?php
declare(strict_types = 1);

namespace Tests\Innmind\Server\Status\Server\Memory;

use Innmind\Server\Status\{
    Server\Memory\Bytes,
    Exception\BytesCannotBeNegative,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class BytesTest extends TestCase
{
    #[DataProvider('steps')]
    public function testInterface($value, $expected)
    {
        $bytes = new Bytes($value);

        $this->assertSame($value, $bytes->toInt());
        $this->assertSame($expected, $bytes->toString());
    }

    public function testThrowWhenNegative()
    {
        $this->expectException(BytesCannotBeNegative::class);

        new Bytes(-1);
    }

    #[DataProvider('strings')]
    public function testFromString($string, $expected)
    {
        $bytes = Bytes::of($string)->match(
            static fn($bytes) => $bytes,
            static fn() => null,
        );

        $this->assertInstanceOf(Bytes::class, $bytes);
        $this->assertSame($expected, $bytes->toString());
    }

    public function testReturnNothingWhenUnknownFormat()
    {
        $this->assertNull(Bytes::of('42Br')->match(
            static fn($bytes) => $bytes,
            static fn() => null,
        ));
    }

    #[DataProvider('invalidStrings')]
    public function testReturnNothingWhenStringTooShort($string)
    {
        $this->assertNull(Bytes::of($string)->match(
            static fn($bytes) => $bytes,
            static fn() => null,
        ));
    }

    public static function steps(): array
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

    public static function strings(): array
    {
        return [
            ['42', '42B'],
            ['42B', '42B'],
            ['42Bi', '42B'],
            ['42K', '42KB'],
            ['42Ki', '42KB'],
            ['42M', '42MB'],
            ['42Mi', '42MB'],
            ['42G', '42GB'],
            ['42Gi', '42GB'],
            ['42T', '42TB'],
            ['42Ti', '42TB'],
            ['42P', '42PB'],
            ['42Pi', '42PB'],
        ];
    }

    public static function invalidStrings(): array
    {
        return [
            [''],
            ['B'],
            ['Bi'],
        ];
    }
}
