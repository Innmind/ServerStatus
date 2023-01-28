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
use Innmind\Immutable\Map;

final class ServerFactory
{
    /**
     * @param Map<non-empty-string, string> $environment
     *
     * @throws UnsupportedOperatingSystem
     */
    public static function build(
        Clock $clock,
        Control $control,
        Map $environment,
    ): Server {
        switch (\PHP_OS) {
            case 'Darwin':
                return new OSX($clock, $control, $environment);

            case 'Linux':
                return new Linux($clock, $control, $environment);
        }

        throw new UnsupportedOperatingSystem;
    }
}
