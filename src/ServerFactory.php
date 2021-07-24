<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

use Innmind\Server\Status\{
    Servers\OSX,
    Servers\Linux,
    Exception\UnsupportedOperatingSystem,
};
use Innmind\TimeContinuum\Clock;

final class ServerFactory
{
    public static function build(Clock $clock): Server
    {
        switch (\PHP_OS) {
            case 'Darwin':
                return new OSX($clock);

            case 'Linux':
                return new Linux($clock);
        }

        throw new UnsupportedOperatingSystem;
    }
}
