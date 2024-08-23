<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use Mediatag\Core\Mediatag;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Chooser.
 */
class Chooser
{
    public static $bypass;

    /**
     * Method changes.
     *
     * @param InputInterface  $input  [explicite description]
     * @param OutputInterface $output [explicite description]
     */
    public static function changes(InputInterface $input, OutputInterface $output, string $questionText = 'Continue with this action'): bool
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $ask      = new QuestionHelper();
        $question = new Question('<question>' . $questionText . '? </question> yY|nN|A|N ');
        $answer   = $ask->ask($input, $output, $question);

        switch ($answer) {
            case 'N':
                self::$bypass = false;

                return false;

            case 'A':
                self::$bypass = true;

                return true;

            case 'a':
                self::$bypass = true;

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
