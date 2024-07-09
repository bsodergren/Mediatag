<?php
/**
 * Command like Metatag writer for video files.
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$fileHeaderComment = <<<'EOF'
Command like Metatag writer for video files.
EOF;
$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
        'binary_operator_spaces' => ['default' => 'align_by_scope'],
    'header_comment' => ['header' => $fileHeaderComment, 'comment_type' => 'PHPDoc', 'location' => 'after_open', 'separate' => 'bottom'],

        'assign_null_coalescing_to_coalesce_equal' => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in(__DIR__)
        // ->exclude([
        //     'folder-to-exclude',
        // ])
        // ->append([
        //     'file-to-include',
        // ])
    )
;
