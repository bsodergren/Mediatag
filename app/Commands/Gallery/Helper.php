<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Gallery;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\GalleryStorageDB;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Traits\Translate;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\Strings;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

trait Helper
{
    public function updateNow()
    {
        // utminfo(func_get_args());

        $data = ['name' => __LIBRARY__.'_last_updated',
            'value'     => parent::$dbconn->dbConn->now(),
            'type'      => 'update'];
        $updateColumns = ['value'];
        $lastInsertId  = 'id';
        parent::$dbconn->dbConn->onDuplicate($updateColumns, $lastInsertId);
        $id = parent::$dbconn->dbConn->insert(__MYSQL_SETTINGS__, $data);
    }

    public function getFileArray()
    {
        // utminfo(func_get_args());

        $this->Deleted_Array = MediaArray::diff($this->db_array, $this->file_array);

        $this->New_Array = MediaArray::diff($this->file_array, $this->db_array);

        // utmdd([__METHOD__,count($this->db_array), count($this->file_array)
        // ,count($this->Deleted_Array), count($this->New_Array)]);
        foreach ($this->file_array as $key => $file) {
            if (\array_key_exists($key, $this->New_Array)) {
                continue;
            }

            if (\array_key_exists($key, $this->Deleted_Array)) {
                continue;
            }

            if (\array_key_exists($key, $this->db_array)) {
                if ($this->file_array[$key] != $this->db_array[$key]) {
                    $this->Changed_Array[$key] = $this->file_array[$key];
                }
            }
        }

        if (Option::istrue('test')) {
            parent::$output->writeln('Deleted files '.print_r($this->Deleted_Array, 1));
            parent::$output->writeln('Changed files '.print_r($this->Changed_Array, 1));
            parent::$output->writeln('New files '.print_r($this->New_Array, 1));
        }

        $changed_string = 0;
        if (\count($this->Changed_Array) > 0) {
            foreach ($this->Changed_Array as $k => $file) {
                $changed_files[] = Strings::getFilePath($file);
            }
            //  $changed_string = implode("\n", $changed_files);
        }

        // utmdd($this->Changed_Array);
        Mediatag::$Console->info(
            'Database Updates',
            ['Files found'   => \count($this->file_array)],
            ['Deleted files' => \count($this->Deleted_Array)],
            ['Changed files' => \count($this->Changed_Array)],
            ['New files'     => \count($this->New_Array)],
        );

        // utmdd([__METHOD__,
        //     'files'   => \count($this->file_array),
        //     'new'     => $this->New_Array,
        //     'changed' => \count($this->Changed_Array),
        //     'deleted' => \count($this->Deleted_Array),
        // ]);

        return [
            'new'     => $this->New_Array,
            'changed' => $this->Changed_Array,
            'deleted' => $this->Deleted_Array,
        ];
    }

    public function removeDBEntry()
    {
        // utminfo(func_get_args());
        foreach ($this->Deleted_Array as $video_key => $video_file) {
            parent::$dbconn->video_key = $video_key;
            parent::$output->writeln('deleting '.basename($video_file).' from db ');
            if (!Option::istrue('preview')) {
                parent::$dbconn->removeDBEntry();
                //  parent::$dbconn->clearDBValues($video_key);
            }
        }
    }

    public function changeDBEntry()
    {
        // utminfo(func_get_args());
        foreach ($this->Changed_Array as $video_key => $video_file) {
            parent::$dbconn->video_file = $video_file;
            // parent::$dbconn->video_key  = $video_key;
            $video_name = basename($video_file);
            if (!Option::istrue('preview')) {
                parent::$output->writeln('Updateing file from db '.$video_name);

                parent::$dbconn->UpdateFilePath();
            } else {
                parent::$dbconn->RowBlock->overwrite('Updateing file '.$video_name.\PHP_EOL);
            }
        }
    }

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
            Mediatag::$output->writeln(Translate::text('L__GALLERY_VIDEO_COUNT', ['VID' => $videos]));
            $ask      = new QuestionHelper();
            $question = new Question(Translate::text('L__GALLERY_ASK_CONTINUE'));

            $answer = $ask->ask(Mediatag::$input, Mediatag::$output, $question);
        }
        // $answer            = 'y';
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

    public function addDBEntry()
    {
        // utminfo(func_get_args());

        $chunkSize = 10;
        $barWidth  = 50;
        $total     = \count($this->New_Array);
        if ($total > 0) {
            $idx                          = 1;
            $progressbar                  = new MediaBar($total, 'three', $barWidth);
            parent::$dbconn->progressbar1 = $progressbar;
            $progressbar->newbar();
            $progressbar->start();

            foreach ($this->New_Array as $video_key => $video_file) {
                // $progressbar->advance();

                $videoDataArray[] = (new GalleryStorageDB())->createDbEntry($video_file, $video_key, $idx, $total);
                ++$idx;
            }
            $idx = 1;

            $data_array = array_chunk($videoDataArray, $chunkSize);
            $chunks     = \count($data_array);
            // utmdd($data_array,$videoDataArray);

            if ($total > $chunkSize) {
                $progressbar2                = new MediaBar($chunks, 'two', $barWidth);
                parent::$dbconn->progressbar = new MediaBar($chunkSize, 'one', $barWidth);
                $progressbar2->newbar()->start();
            }

            foreach ($data_array as $data) {
                if ($total > $chunkSize) {
                    parent::$dbconn->progressbar->newbar()->start();
                    $progressbar2->advance();
                }

                // if (Option::istrue('preview')) {
                //     //   $video_string = [];
                //     foreach ($data as $k => $row) {
                //         $video_string[] = '<info>'.$idx++.'</info> : Video <comment>'.$row['filename'].'</comment> added to db ';
                //     }

                //     $video_string[] = ' '.\PHP_EOL;
                //     //    parent::$dbconn->RowBlock->overwrite($video_string);
                // } else {
                parent::$dbconn->addDBArray($data);

                // }
            }
            $this->updateNow();
        }
    }
}
