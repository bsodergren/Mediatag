<?php
/**
 * Command like Metatag writer for video files.
 */

 namespace Mediatag\Commands\Clip\Helpers;

use Mediatag\Commands\Clip\Markers\Markers as MarkerHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Data\Markers;
use Mediatag\Traits\ffmpegTransition;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Traits\Translate;
use Mediatag\Utilities\Chooser;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

trait DeleteHelper
{

    public function deleteClips()
    {
        Translate::$Class = __CLASS__;

        $directory  = $this->getClipDirectory(__CURRENT_DIRECTORY__, 0);
        $file_array = Mediatag::$finder->Search($directory, '*.mp4');

        $videos = \count($file_array);
        // $question = new Question(Translate::text('L__CLIP_ASK_CONTINUE'));
        // Mediatag::$output->writeln();

        $go = Chooser::changes(Translate::text('L__CLIP_VIDEO_COUNT', ['VID' => $videos]), 'yes', __LINE__);

        if (true === $go) {
            Mediatag::$output->writeln('Deleting '.$videos.' entrys in the DB');
            foreach ($file_array as $file) {
                Mediatag::$output->writeLn('<info> removing file '.basename($file).'</info>');
                //Mediatag::$filesystem->remove($file);
                utmdump($file);
            }
        }
    }

}
