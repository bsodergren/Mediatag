<?php
/**
 * Command like Metatag writer for video files.
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$fileHeaderComment = <<<'EOF'
Command like Metatag writer for video files.
EOF;

/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.64.0|configurator
 * you can change this configuration by importing this file.
 */

$config = new Config();
return $config
->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0'                               => true,
        '@PER-CS2.0:risky'                         => true,
        'binary_operator_spaces'                   => [
            'default'   => 'align_single_space_minimal',
            'operators' => ['=>' => 'align_by_scope'],
        ],
        'header_comment'                           => ['header' => $fileHeaderComment, 'comment_type' => 'PHPDoc', 'location' => 'after_open', 'separate' => 'bottom'],
        'array_indentation'                        => true,
        'assign_null_coalescing_to_coalesce_equal' => true,
    ])
    ->setFinder(
        Finder::create()
        ->in(__DIR__),
        // ->exclude([
        //     'folder-to-exclude',
        // ])
        // ->append([
        //     'file-to-include',
        // ])
    );
