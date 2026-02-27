<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp;

/**
 * Result of a grep operation across files.
 */
final class GrepResult
{
    /**
     * @param  array<string,string[]>  $matches  Map of logicalPath => list of full-match strings
     * @param  GrepError[]  $errors  Pattern/matching errors observed during the run
     */
    public function __construct(
        public array $matches = [],
        public array $errors = []
    ) {}

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
