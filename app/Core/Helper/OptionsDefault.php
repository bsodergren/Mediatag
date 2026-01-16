<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

trait OptionsDefault
{
    public static function getDisplayOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;
        $cmdName          = ucfirst(str_replace('media', '', __SCRIPT_NAME__));
        $options          = [
            ['show', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_SHOW', ['TXT' => $cmdName])],
            ['hide', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_HIDE', ['TXT' => $cmdName])],
            ['add', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_ADD', ['TXT' => $cmdName])],
            ['drop', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_DROP', ['TXT' => $cmdName])],
        ];

        return self::getOptions($options);
    }

    public static function getDefaultOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        $options = [
            ['filelist', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__DEFAULT_FILELIST')],
            ['numberofFiles', 'N', InputOption::VALUE_NONE, Translate::text('L__DEFAULT_NUMBEROFFILES')],
            ['max', 'M', InputOption::VALUE_REQUIRED, Translate::text('L__DEFAULT_MAX')],
            ['range', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__DEFAULT_RANGE')],
            ['filenumber', 'F', InputOption::VALUE_REQUIRED, Translate::text('L__DEFAULT_FILENUMBER')],
            ['new', '', InputOption::VALUE_NONE, Translate::text('L__DEFAULT_SHOW_NEWFILES')],
        ];

        return self::getOptions($options);
    }

    public static function getQuestionOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        $options = [
            ['ask', '', InputOption::VALUE_NONE, Translate::text('L__DEFAULT_ASK_FILE')],
            ['overwrite', 'o', InputOption::VALUE_NONE, Translate::text('L__DEFAULT_OVERWRITE_FILE')],
            ['yes', 'y', InputOption::VALUE_NONE, Translate::text('L__DEFAULT_QUESTION_YES')],
        ];

        return self::getOptions($options);
    }

    public static function getTestOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        $options = [
            ['test', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_CMD')],
            ['preview', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_PREVIEW')],
            ['time', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_TIME')],
            ['dump', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_DUMP')],
            ['flush', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_FLUSH')],
            ['nocache', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_FLUSH')],
            ['no-progress', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_NO_PROGRESS')],
            // ['trunc',null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_TRUNC')],
        ];

        return self::getOptions($options);
    }

    public static function getMetaOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;
        $cmdName          = ucfirst(str_replace('media', '', __SCRIPT_NAME__));
        $options          = [
            ['only', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__META_ONLY',

                ['TXT' => $cmdName]), [], ['Studio', 'Genre', 'Title', 'Artist', 'Keyword']],
            ['title', 't', InputOption::VALUE_REQUIRED, Translate::text('L__META_TITLE', ['TXT' => $cmdName])],
            ['genre', 'g', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__META_GENRE', ['TXT' => $cmdName])],
            ['studio', 's', InputOption::VALUE_REQUIRED, Translate::text('L__META_STUDIO', ['TXT' => $cmdName])],
            ['network', 'n', InputOption::VALUE_REQUIRED, Translate::text('L__META_NETWORK', ['TXT' => $cmdName])],

            ['artist', 'a', InputOption::VALUE_REQUIRED, Translate::text('L__META_ARTIST', ['TXT' => $cmdName])],
            ['keyword', 'k', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__META_KEYWORD', ['TXT' => $cmdName])],
        ];

        return self::getOptions($options);
    }
}
