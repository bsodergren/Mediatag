<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Matcher;

/**
 * Result of a pattern match operation.
 * Carries both the matches and optional error information from the matcher.
 */
final readonly class MatchResult
{
    /**
     * @param string[] $matches Full-match strings
     * @param int|null $errorCode PCRE error code (preg_last_error), when available
     * @param string|null $errorMessage Human-readable message, when available
     */
    public function __construct(
        public array $matches,
        public ?int $errorCode = null,
        public ?string $errorMessage = null
    ) {}

    public function hasError(): bool
    {
        return $this->errorCode !== null;
    }
}
