<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\ProcessRunner;

/**
 * Immutable result of a process execution.
 */
final readonly class ProcessResult
{
    public function __construct(
        public int $exitCode,
        public string $stdout,
        public string $stderr
    ) {}
}
