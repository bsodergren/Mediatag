<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use Mediatag\Locales\Lang;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class MediaApplication extends Application
{
    use Lang;
    use Translate;

    protected function getDefaultInputDefinition(): InputDefinition
    {
        Translate::$Class = __CLASS__;

        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, Translate::text('L__APP_DEFAULT_CMD').\PHP_EOL.\PHP_EOL.\PHP_EOL.\PHP_EOL.\PHP_EOL),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_HELP')),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_QUIET')),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_VERBOSE')),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_VERSION')),
            new InputOption('--ansi', '', InputOption::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', null),
            new InputOption('--no-interaction', '', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_NOASK')),
            new InputOption('--path', '', InputOption::VALUE_REQUIRED, Translate::text('L__APP_DEFAULT_PATH')),
        ]);
    }
}
