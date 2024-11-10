<?php
/**
 * Command like Metatag writer for video files.
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$fileHeaderComment = <<<'EOF'
Command like Metatag writer for video files.
EOF;

/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.64.0|configurator
 * you can change this configuration by importing this file.
 */
$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        'header_comment'                           => ['header' => $fileHeaderComment, 'comment_type' => 'PHPDoc', 'location' => 'after_open', 'separate' => 'bottom'],

        // Each line of multi-line DocComments must have an asterisk [PSR-5] and must be aligned with the first one.
        'align_multiline_comment' => ['comment_type' => 'all_multiline'],
        // Each element of an array must be indented exactly once.
        'array_indentation' => true,
        // Converts simple usages of `array_push($x, $y);` to `$x[] = $y;`.
        'array_push' => true,
        // PHP arrays should be declared using the configured syntax.
        'array_syntax' => true,
        // The body of each control structure MUST be enclosed within braces.
        'control_structure_braces' => true,
        // Control structure continuation keyword must be on the configured line.
        'control_structure_continuation_position' => ['position' => 'next_line'],
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__)
        // ->exclude([
        //     'folder-to-exclude',
        // ])
        // ->append([
        //     'file-to-include',
        // ])
    );
// ;

// /*
//  * This document has been generated with
//  * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.59.3|configurator
//  * you can change this configuration by importing this file.
//  */
// $config = new PhpCsFixer\Config();
// return $config
// ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
//     ->setRiskyAllowed(true)
//     ->setRules([
//         '@PER-CS2.0'                               => true,
//         '@PER-CS2.0:risky'                         => true,
//         'binary_operator_spaces'                   => [
//             'default'   => 'align_single_space_minimal',
//             'operators' => ['=>' => 'align_by_scope'],
//         ],
//         'header_comment'                           => ['header' => $fileHeaderComment, 'comment_type' => 'PHPDoc', 'location' => 'after_open', 'separate' => 'bottom'],
//         'array_indentation'                        => true,
//         'assign_null_coalescing_to_coalesce_equal' => true,
//     ])
//     ->setFinder(
//         PhpCsFixer\Finder::create()
//         ->in(__DIR__),
//         // ->exclude([
//         //     'folder-to-exclude',
//         // ])
//         // ->append([
//         //     'file-to-include',
//         // ])
//     );
