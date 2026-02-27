<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Matcher;

/**
 * PatternMatcherInterface defines how to extract matches from a text using a pattern.
 *
 * Implementations must report PCRE warnings/errors via the returned MatchResult
 * while still returning any collected matches when possible. Callers can inspect
 * the error fields but do not have to handle them yet.
 */
interface PatternMatcherInterface
{
    /**
     * Find all matches of the given pattern in the provided content.
     *
     * @param  string  $content  The text to search in
     * @param  string  $pattern  A PCRE pattern (e.g., '/foo/i')
     * @return MatchResult Result containing matches and optional error info
     */
    public function matchAll(string $content, string $pattern): MatchResult;
}
