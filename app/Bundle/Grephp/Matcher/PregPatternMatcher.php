<?php

declare(strict_types=1);

namespace Mediatag\Bundle\Grephp\Matcher;

/**
 * Default PatternMatcher implementation that relies on PHP's PCRE engine.
 */
final class PregPatternMatcher implements PatternMatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function matchAll(string $content, string $pattern): MatchResult
    {
        $matches = [];
        $hadWarning = false;
        $warningMsg = null;
        set_error_handler(function (int $errno, string $errstr) use (&$hadWarning, &$warningMsg): bool {
            // Capture any PCRE warnings (e.g., invalid pattern) without emitting
            $hadWarning = true;
            $warningMsg = $errstr;
            return true; // we handled it
        });
        $ok = preg_match_all($pattern, $content, $matches);
        restore_error_handler();

        if ($ok === false || $hadWarning) {
            // Prefer preg_last_error[_msg] when available
            $code = function_exists('preg_last_error') ? preg_last_error() : null;
            $msg = null;
            if (function_exists('preg_last_error_msg')) {
                $msg = preg_last_error_msg();
            } elseif (is_string($warningMsg)) {
                $msg = $warningMsg;
            }
            return new MatchResult(matches: [], errorCode: $code, errorMessage: $msg);
        }
        $full = $matches[0] ?? [];
        return new MatchResult(matches: $full, errorCode: null, errorMessage: null);
    }
}
