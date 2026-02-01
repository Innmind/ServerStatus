<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server;

use Innmind\Server\Status\Server\Disk\{
    Implementation,
    Unix,
    Logger,
    Volume,
    Volume\MountPoint,
};
use Innmind\Server\Control\Server\Processes;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};
use Psr\Log\LoggerInterface;

final class Disk
{
    private function __construct(
        private Implementation $implementation,
    ) {
    }

    /**
     * @internal
     */
    public static function of(Processes $processes): self
    {
        return new self(Unix::of($processes));
    }

    /**
     * @internal
     */
    public static function logger(self $disk, LoggerInterface $logger): self
    {
        return new self(Logger::of(
            $disk->implementation,
            $logger,
        ));
    }

    /**
     * @return Sequence<Volume>
     */
    public function volumes(): Sequence
    {
        return $this->implementation->volumes();
    }

    /**
     * @return Maybe<Volume>
     */
    public function get(MountPoint $point): Maybe
    {
        return $this->implementation->get($point);
    }
}
