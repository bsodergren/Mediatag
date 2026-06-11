<?php

namespace Mediatag\Commands\Db\Commands\Remove;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\VideoInfo\Section\preview\GifPreviewFiles;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use Mediatag\Traits\Translate;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\Strings;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function is_array;

trait RemoveHelper
{
    public function removeEntry()
    {
        if (Option::isTrue('filelist')) {
            if (count($this->db_array) > 0) {
                foreach ($this->db_array as $video_key => $video_file) {
                    StorageDB::$DB->video_key = $video_key;
                    parent::$output->writeln('deleting ' . basename($video_file) . ' from db ');
                    if (! Option::istrue('preview')) {
                        StorageDB::$DB->removeDBEntry();
                        StorageDB::$DB->clearDBValues($video_key);
                    }
                }
                Mediatag::$Console->writeln(count($this->db_array) . ' files removed');
                exit;
            }
        }
        Mediatag::$Console->writeln('No files given');
        exit;
    }
}
