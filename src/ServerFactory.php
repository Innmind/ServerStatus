<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

use Innmind\Server\Status\{
    Servers\OSX,
    Servers\Linux,
    Exception\UnsupportedOperatingSystem
};
use Innmind\TimeContinuum\TimeContinuumInterface;

final class ServerFactory
{
    private $clock;

    public function __construct(TimeContinuumInterface $clock)
    {
        $this->clock = $clock;
    }

    public function make(): Server
    {
        switch (PHP_OS) {
            case 'Darwin':
                return new OSX($this->clock);

            case 'Linux':
                return new Linux($this->clock);
        }

        throw new UnsupportedOperatingSystem;
    }
}
