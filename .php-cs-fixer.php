<?php

/**
 * Command like Metatag writer for video files.
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use UTM\Rules;

Rules::setFileHeaderComment('Command like Metatag writer for video files.');

$config = new Config();

return $config
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules(Rules::getCsFixerRules())
    ->setFinder(
        Finder::create()
            ->in(__DIR__),
    );
