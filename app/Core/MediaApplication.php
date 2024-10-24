<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use Mediatag\Core\Mediatag;


use Mediatag\Locales\Lang;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Bundle\Monolog\UTMLog;

class MediaApplication extends Application
{
    use Lang;
    use Translate;

    protected function getDefaultInputDefinition(): InputDefinition
    {

        utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, Translate::text('L__APP_DEFAULT_CMD')),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_HELP')),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_QUIET')),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_VERBOSE')),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_VERSION')),
            new InputOption('--ansi', '', InputOption::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', null),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_NOASK')),
            new InputOption('--path', '', InputOption::VALUE_REQUIRED, Translate::text('L__APP_DEFAULT_PATH')),
        ]);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        utminfo();
        parent::run($input, $output);
    }
}
