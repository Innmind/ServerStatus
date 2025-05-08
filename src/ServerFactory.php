<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

use Innmind\Server\Status\Servers\{
    OSX,
    Linux,
};
use Innmind\Server\Control\Server as Control;
use Innmind\TimeContinuum\Clock;

final class ServerFactory
{
    public static function build(
        Clock $clock,
        Control $control,
        EnvironmentPath $path,
    ): Server {
        return match (\PHP_OS) {
            'Darwin' => new OSX($clock, $control, $path),
            'Linux' => new Linux($clock, $control),
            default => throw new \LogicException('Unsupported operating system '.\PHP_OS),
        };
    }
}
