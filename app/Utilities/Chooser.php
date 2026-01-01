<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

use function array_key_exists;
use function is_array;

/**
 * Chooser.
 */
class Chooser
{
    public static $bypass = [];

    public static $QuestionFormat = '<fg=bright-yellow;options=reverse>%text%</>';

    public static function FormatQuestion($format)
    {
        self::$QuestionFormat = $format;
    }

    private static function getQuestion($Question)
    {
        if (is_array($Question)) {
            if (array_key_exists('text', $Question)) {
                $msgText = $Question['text'];
                unset($Question['text']);
            }

            foreach ($Question as $key => $var) {
                $msgText = str_replace('%' . $key . '%', $var, $msgText);
            }
        } else {
            $msgText = $Question;
        }

        return str_replace('%text%', $msgText, self::$QuestionFormat);
    }

    /**
     * Method changes.
     */
    public static function changes(
        string $questionText = 'Continue with this action?',
        $optionName = 'yes',
        $bypass_id = 25
    ): bool {
        $bypass_id = $optionName . '_' . $bypass_id;



        // utmdd([Option::isTrue('yes'), Option::isTrue('overwrite'), Option::isTrue('ask')]);

        if ($optionName != 'yes') {
            if (Option::isFalse('ask')) {
                if (Option::isTrue('overwrite') || Option::isTrue('yes')) {
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

        $ask      = new QuestionHelper;
        $question = new Question(self::getQuestion($questionText) . ' yY|nN|A|N: ');
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

    public static function AskQuestion($questionText = '', $Answers = [], $default = null)
    {
        $Answers              = array_merge($Answers, ['Exit']);
        $questionFormatedText = self::getQuestion($questionText);
        // $text = Mediatag::$output->writeln($questionFormatedText );

        // utmdd( $text,"ex");
        $ask      = new QuestionHelper;
        $question = new ChoiceQuestion($questionFormatedText, $Answers, $default);
        $question->setAutocompleterValues($Answers);
        $answer = $ask->ask(Mediatag::$input, Mediatag::$output, $question);

        // utmdd(get_class_methods(get_class(Mediatag::$output)));

        if ($answer == 'Exit') {
            return false;
        }

        return $answer;
    }
}
