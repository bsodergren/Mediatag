<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

use function array_key_exists;

/**
 * Chooser.
 */
class Chooser
{
    public static $bypass = [];

    /**
     * Method changes.
     */
    public static function changes(string $questionText = 'Continue with this action', $optionName = 'yes', $bypass_id = 25): bool
    {
        $bypass_id = $optionName.'_'.$bypass_id;

        if ('yes' != $optionName) {
            if (Option::isFalse('overwrite')) {
                if (Option::isFalse('ask')) {
                    return true;
                }
            }
        }

        if (array_key_exists($bypass_id, self::$bypass)) {
            return self::$bypass[$bypass_id];
        }

        if (Option::isTrue($optionName)) {
            return true;
        }

        $ask      = new QuestionHelper();
        $question = new Question('<fg=bright-yellow;options=reverse>'.$questionText.'? </> yY|nN|A|N:');
        $answer   = $ask->ask(Mediatag::$input, Mediatag::$output, $question);

        switch ($answer) {
            case 'N':
                self::$bypass[$bypass_id] = false;

                return false;
            case 'A':
                self::$bypass[$bypass_id] = true;

                return true;

            case 'a':
                self::$bypass[$bypass_id] = true;

                return true;
            case 'y':
                return true;

            case 'Y':
                return true;
            default:
                return false;
        }
    }
}
