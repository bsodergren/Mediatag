<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\EmptyDB;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

trait Helper
{


    public function execEmpty()
    {
        // utminfo(func_get_args());

        Translate::$Class             = __CLASS__;
        Mediatag::$dbconn->file_array = Mediatag::$SearchArray;
        $videos                       = Mediatag::$dbconn->getVideoCount();

        if (Option::istrue('yes')) {
            $go     = true;
            $answer = 'y';
        } else {
            Mediatag::$output->writeln(Translate::text('L__DB_VIDEO_COUNT', ['VID' => $videos]));
            $ask      = new QuestionHelper();
            $question = new Question(Translate::text('L__DB_ASK_CONTINUE'));

            $answer = $ask->ask(Mediatag::$input, Mediatag::$output, $question);
        }
        switch ($answer) {
            case 'y':
                $go = true;

                break;

            case 'Y':
                $go = true;

                break;

            default:
                $go = false;

                break;
        }

        if (true == $go) {
            Mediatag::$output->writeln('Deleting '.$videos.' entrys in the DB');
            Mediatag::$dbconn->emptydatabase();
        }
    }
}
