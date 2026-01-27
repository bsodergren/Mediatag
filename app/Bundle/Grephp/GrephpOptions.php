<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp;

/**
 * Configuration options for Grephp runtime behavior.
 */
final class GrephpOptions
{
    public const STREAM_MODE_LINE = 'line';
    public const STREAM_MODE_CHUNK = 'chunk';

    public function __construct(
        public readonly ?int $streamThresholdBytes = 5_000_000,
        public readonly string $streamMode = self::STREAM_MODE_LINE,
        public readonly int $chunkSize = 64 * 1024,
        public readonly int $chunkOverlap = 1024,
        public readonly ?int $maxMatchPerFile = null,
        // New: batched line processing options (used in STREAM_MODE_LINE)
        public readonly int $linesPerBatch = 1000,
        public readonly int $linesBatchOverlap = 0
    ) {
    }
}
