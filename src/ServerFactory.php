<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

use Innmind\Server\Control\Server as Control;
use Innmind\Time\Clock;

final class ServerFactory
{
    #[\NoDiscard]
    public static function build(
        Clock $clock,
        Control $control,
        EnvironmentPath $path,
    ): Server {
        return match (\PHP_OS) {
            'Darwin' => Server::osx($clock, $control, $path),
            'Linux' => Server::linux($clock, $control),
            default => throw new \LogicException('Unsupported operating system '.\PHP_OS),
        };
    }
}
