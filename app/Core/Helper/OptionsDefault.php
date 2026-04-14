<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use JBZoo\Cli\CliHelper;
use JBZoo\Cli\OutputMods\AbstractOutputMode;
use JBZoo\Cli\OutputMods\Cron;
use JBZoo\Cli\OutputMods\Logstash;
use JBZoo\Cli\OutputMods\Text;
use JBZoo\Cli\ProgressBars\AbstractProgressBar;
use JBZoo\Utils\Arr;
use JBZoo\Utils\Str;
use JBZoo\Utils\Vars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use function JBZoo\Utils\bool;
use function JBZoo\Utils\float;
use function JBZoo\Utils\int;

trait OptionsDefault
{
    public static function getDisplayOptions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;
        $cmdName     = ucfirst(str_replace('media', '', __SCRIPT_NAME__));
        $options     = [
            ['show', '', InputOption::VALUE_NONE, self::text('L__DISPLAY_SHOW', ['TXT' => $cmdName])],
            ['hide', '', InputOption::VALUE_NONE, self::text('L__DISPLAY_HIDE', ['TXT' => $cmdName])],
            ['add', '', InputOption::VALUE_NONE, self::text('L__DISPLAY_ADD', ['TXT' => $cmdName])],
            ['drop', '', InputOption::VALUE_NONE, self::text('L__DISPLAY_DROP', ['TXT' => $cmdName])],
        ];

        return self::getOptions($options);
    }

    public static function getDefaultOptions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        $options = [
            ['filelist', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__DEFAULT_FILELIST')],
            ['numberofFiles', 'N', InputOption::VALUE_NONE, self::text('L__DEFAULT_NUMBEROFFILES')],
            ['max', 'M', InputOption::VALUE_REQUIRED, self::text('L__DEFAULT_MAX')],
            ['range', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__DEFAULT_RANGE')],
            ['filenumber', 'F', InputOption::VALUE_REQUIRED, self::text('L__DEFAULT_FILENUMBER')],
            ['new', '', InputOption::VALUE_NONE, self::text('L__DEFAULT_SHOW_NEWFILES')],
        ];

        return self::getOptions($options);
    }

    public static function getQuestionOptions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        $options = [
            ['ask', '', InputOption::VALUE_NONE, self::text('L__DEFAULT_ASK_FILE')],
            ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L__DEFAULT_OVERWRITE_FILE')],
            ['yes', 'y', InputOption::VALUE_NONE, self::text('L__DEFAULT_QUESTION_YES')],
        ];

        return self::getOptions($options);
    }

    public static function getTestOptions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        $options = [
            ['test', null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_CMD')],
            ['preview', null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_PREVIEW')],
            ['time', null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_TIME')],
            ['dump', null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_DUMP')],
            ['flush', null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_FLUSH')],
            ['nocache', null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_FLUSH')],
            ['no-progress', null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_NO_PROGRESS')],
            // ['trunc',null, InputOption::VALUE_NONE, self::text('L__DEFAULT_TEST_TRUNC')],
        ];

        return self::getOptions($options);
    }

    public static function getMetaOptions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;
        $cmdName     = ucfirst(str_replace('media', '', __SCRIPT_NAME__));
        $options     = [
            ['only', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__META_ONLY',

                ['TXT' => $cmdName]), [], ['Studio', 'Genre', 'Title', 'Artist', 'Keyword']],
            ['title', 't', InputOption::VALUE_OPTIONAL, self::text('L__META_TITLE', ['TXT' => $cmdName])],
            ['genre', 'g', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__META_GENRE', ['TXT' => $cmdName])],
            ['studio', 's', InputOption::VALUE_OPTIONAL, self::text('L__META_STUDIO', ['TXT' => $cmdName])],
            ['network', '', InputOption::VALUE_OPTIONAL, self::text('L__META_NETWORK', ['TXT' => $cmdName])],

            ['artist', 'a', InputOption::VALUE_OPTIONAL, self::text('L__META_ARTIST', ['TXT' => $cmdName])],
            ['keyword', 'k', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__META_KEYWORD', ['TXT' => $cmdName])],
        ];

        return self::getOptions($options);
    }

    public static function getCliCmdOptions()
    {
        self::$Class = __CLASS__;
        $cmdName     = ucfirst(str_replace('media', '', __SCRIPT_NAME__));
        $options     = [
            ['no-progress', null, InputOption::VALUE_NONE, 'Disable progress bar animation for logs. ' . 'It will be used only for <info>' . Text::getName() . '</info> output format.'],
            ['mute-errors', null, InputOption::VALUE_NONE, "Mute any sort of errors. So exit code will be always \"0\" (if it's possible).\n" . "It has major priority then <info>--non-zero-on-error</info>. It's on your own risk!"],
            ['stdout-only', null, InputOption::VALUE_NONE, "For any errors messages application will use StdOut instead of StdErr. It's on your own risk!"],
            ['non-zero-on-error', null, InputOption::VALUE_NONE, 'None-zero exit code on any StdErr message.'],
            ['timestamp', null, InputOption::VALUE_NONE, 'Show timestamp at the beginning of each message.' . 'It will be used only for <info>' . Text::getName() . '</info> output format.'],
            ['profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information.'],

            ['output-mode', null, InputOption::VALUE_REQUIRED, "Output format. Available options:\n" .
            CliHelper::renderListForHelpDescription([Text::getName() => Text::getDescription(),
                Cron::getName()                                      => Cron::getDescription(),
                Logstash::getName()                                  => Logstash::getDescription(), ]), Text::getName()],
            [Cron::getName(), null, InputOption::VALUE_NONE, 'Alias for <info>--output-mode=' . Cron::getName() . '</info>. <comment>Deprecated!</comment>'],
        ];

        return self::getOptions($options);
    }
}
