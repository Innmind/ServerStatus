<?php
declare(strict_types = 1);

namespace Innmind\Server\Status\Server\Disk;

use Innmind\Server\Status\Server\Disk\Volume\MountPoint;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class Logger implements Implementation
{
    private function __construct(
        private Implementation $disk,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @internal
     */
    public static function of(Implementation $disk, LoggerInterface $logger): self
    {
        return new self($disk, $logger);
    }

    #[\Override]
    public function volumes(): Sequence
    {
        $volumes = $this->disk->volumes();
        $this->logger->debug('{count} volumes currently mounted', [
            'count' => $volumes->size(),
            'volumes' => $volumes->reduce(
                [],
                function(array $volumes, Volume $volume): array {
                    $volumes[] = $this->normalize($volume);

                    return $volumes;
                },
            ),
        ]);

        return $volumes;
    }

    #[\Override]
    public function get(MountPoint $point): Maybe
    {
        return $this
            ->disk
            ->get($point)
            ->map(function($volume) {
                $this->logger->debug(
                    'Volume currently mounted at {point}',
                    $this->normalize($volume),
                );

                return $volume;
            });
    }

    private function normalize(Volume $volume): array
    {
        return [
            'point' => $volume->mountPoint()->toString(),
            'size' => $volume->size()->toString(),
            'available' => $volume->available()->toString(),
            'used' => $volume->used()->toString(),
            'usage' => $volume->usage()->toString(),
        ];
    }
}
