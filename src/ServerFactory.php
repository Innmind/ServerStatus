<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

use Innmind\Server\Status\{
    Servers\OSX,
    Servers\Linux,
    Exception\UnsupportedOperatingSystem,
};
use Innmind\Server\Control\Server as Control;
use Innmind\TimeContinuum\Clock;

final class ServerFactory
{
    /**
     * @throws UnsupportedOperatingSystem
     */
    public static function build(
        Clock $clock,
        Control $control,
        EnvironmentPath $path,
    ): Server {
        switch (\PHP_OS) {
            case 'Darwin':
                return new OSX($clock, $control, $path);

            case 'Linux':
                return new Linux($clock, $control);
        }

        throw new UnsupportedOperatingSystem;
    }
}
