<?php
declare(strict_types = 1);

namespace Innmind\Server\Status;

use Innmind\Server\Status\{
    Servers\Implementation,
    Servers\OSX,
    Servers\Linux,
    Servers\Logger,
    Server\Cpu,
    Server\Memory,
    Server\Processes,
    Server\LoadAverage,
    Server\Disk,
};
use Innmind\Server\Control\Server as Control;
use Innmind\TimeContinuum\Clock;
use Innmind\Url\Path;
use Innmind\Immutable\Attempt;
use Psr\Log\LoggerInterface;

final class Server
{
    private function __construct(
        private Implementation $implementation,
    ) {
    }

    public static function osx(
        Clock $clock,
        Control $control,
        EnvironmentPath $path,
    ): self {
        return new self(OSX::of($clock, $control, $path));
    }

    public static function linux(
        Clock $clock,
        Control $control,
    ): self {
        return new self(Linux::of($clock, $control));
    }

    public static function logger(self $server, LoggerInterface $logger): self
    {
        return new self(Logger::of(
            $server->implementation,
            $logger,
        ));
    }

    /**
     * @return Attempt<Cpu>
     */
    public function cpu(): Attempt
    {
        return $this->implementation->cpu();
    }

    /**
     * @return Attempt<Memory>
     */
    public function memory(): Attempt
    {
        return $this->implementation->memory();
    }

    public function processes(): Processes
    {
        return $this->implementation->processes();
    }

    /**
     * @return Attempt<LoadAverage>
     */
    public function loadAverage(): Attempt
    {
        return $this->implementation->loadAverage();
    }

    public function disk(): Disk
    {
        return $this->implementation->disk();
    }

    public function tmp(): Path
    {
        return $this->implementation->tmp();
    }
}
