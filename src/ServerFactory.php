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
    private $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function __invoke(): Server
    {
        switch (\PHP_OS) {
            case 'Darwin':
                return new OSX($this->clock);

            case 'Linux':
                return new Linux($this->clock);
        }

        throw new UnsupportedOperatingSystem;
    }

    public static function build(Clock $clock): Server
    {
        return (new self($clock))();
    }
}
