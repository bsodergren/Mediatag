<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Executable\YoutubeExec;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\VideoData\Data\Duration;
use Mediatag\Modules\VideoData\Data\Markers;
use Mediatag\Modules\VideoData\Data\preview\GifPreviewFiles;
use Mediatag\Modules\VideoData\Data\Thumbnail;
use Mediatag\Modules\VideoData\Data\VideoInfo;
use Mediatag\Traits\Translate;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\Strings;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

trait Helper
{
    public function updateNow()
    {
        utminfo(func_get_args());

        $data          = ['name' => __LIBRARY__ . '_last_updated',
            'value'              => parent::$dbconn->dbConn->now(),
            'type'               => 'update'];
        $updateColumns = ['value'];
        $lastInsertId  = 'id';
        parent::$dbconn->dbConn->onDuplicate($updateColumns, $lastInsertId);
        $id            = parent::$dbconn->dbConn->insert(__MYSQL_SETTINGS__, $data);
    }

    public function lastUpdated()
    {
        utminfo(func_get_args());

        $db  = Mediatag::$dbconn->dbConn;
        $db->where('name', __LIBRARY__ . '_last_updated');

        $res = $db->getValue(__MYSQL_SETTINGS__, 'value');
        $q   = $db->getLastQuery();

        return $res;
        //        utmdd([__METHOD__,$res]);
    }

    public static function getNewFiles(array $array, InputInterface $input, OutputInterface $output): array
    {
        utminfo(func_get_args());

        $obj = (new self($input, $output))->init()->getFileArray();

        return [
            'New'     => $obj['new'],
            'Changed' => $obj['changed'],
        ];
    }

    public function getFileArray()
    {
        utminfo(func_get_args());


        $this->Deleted_Array = MediaArray::diff($this->db_array, $this->file_array);


        $this->New_Array     = MediaArray::diff($this->file_array, $this->db_array);

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
            parent::$output->writeln('Deleted files ' . print_r($this->Deleted_Array, 1));
            parent::$output->writeln('Changed files ' . print_r($this->Changed_Array, 1));
            parent::$output->writeln('New files ' . print_r($this->New_Array, 1));
        }

        $changed_string      = 0;
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
            ['Changed files' => $changed_string],
            ['New files'     => \count($this->New_Array)],
        );

        // utmdd([__METHOD__,
        //     'files' => count($this->file_array),
        //      'new' => $this->New_Array,
        //      'changed' => count($this->Changed_Array),
        //      'deleted' => count($this->Deleted_Array),
        //  ]);

        return [
            'new'     => $this->New_Array,
            'changed' => $this->Changed_Array,
            'deleted' => $this->Deleted_Array,
        ];
    }

    public function execEmpty()
    {
        utminfo(func_get_args());

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

            $answer   = $ask->ask(Mediatag::$input, Mediatag::$output, $question);
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
            Mediatag::$output->writeln('Deleting ' . $videos . ' entrys in the DB');
            Mediatag::$dbconn->emptydatabase();
        }
    }

    public function execThumb()
    {
        utminfo(func_get_args());

        $this->obj = new Thumbnail();
        // $this->obj = new Thumbnail(parent::$input, parent::$output);
        $this->obj->updateVideoData();
    }

    public function execMarkers()
    {
        utminfo(func_get_args());

        $this->obj = new Markers();
        // $this->obj = new Thumbnail(parent::$input, parent::$output);
        $this->obj->updateVideoData();
    }

    public function execDuration()
    {
        utminfo(func_get_args());

        $this->obj = new Duration();
        $this->obj->updateVideoData();
    }

    public function execInfo()
    {
        utminfo(func_get_args());

        $this->obj = new VideoInfo();
        $this->obj->updateVideoData();
    }

    public function execPreview()
    {
        utminfo(func_get_args());

        $this->obj = new GifPreviewFiles();
        $this->obj->updateVideoData();
    }

    public function checkClean()
    {
        utminfo(func_get_args());

        if (Option::istrue('clean')) {
            $this->obj->clean();
        } elseif (Option::istrue('empty')) {
            // $this->obj->clearDBValues();
        }
    }

    /**
     * Summary of updateEntry.
     *
     * @param mixed|null $exists
     */
    public function updateEntry($key, $video_file, $exists = null)
    {
        utminfo(func_get_args());

        $this->OutputText   = [];
        $this->OutputText[] = '<info>' . $this->count . '</info>:<comment>' . basename($video_file) . '</comment> ';

        if (null !== parent::$dbconn->videoExists($key, 'thumbnail')) {
            $this->thumb->get($key, $video_file);
            $this->OutputText[] = "\t<fg=bright-cyan>" . $this->thumb->getVideoText() . '</> ';
        }

        if (null !== parent::$dbconn->videoExists($key, 'duration')) {
            $this->duration->get($key, $video_file);
            $this->OutputText[] = "\t<fg=bright-cyan>" . $this->duration->getVideoText() . '</> ';
        }
        if ($exists == parent::$dbconn->videoExists($key, null, __MYSQL_VIDEO_INFO__)) {
            $this->vinfo->get($key, $video_file);
            $this->OutputText[] = "\t<fg=cyan>" . $this->vinfo->getVideoText() . '</> ';
        }

        Mediatag::$output->writeln($this->OutputText);
    }

    public function removeDBEntry()
    {
        utminfo(func_get_args());

        foreach ($this->Deleted_Array as $video_key => $video_file) {
            parent::$dbconn->video_key = $video_key;
            parent::$output->writeln('deleting ' . basename($video_file) . ' from db ');
            if (! Option::istrue('preview')) {
                parent::$dbconn->removeDBEntry();
                //  parent::$dbconn->clearDBValues($video_key);
            }
        }
    }

    public function addDBEntry()
    {
        utminfo(func_get_args());

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
                //$progressbar->advance();

                $videoDataArray[] = (new StorageDB())->createDbEntry($video_file, $video_key, $idx, $total);
                ++$idx;
            }
            $idx                          = 1;

            $data_array                   = array_chunk($videoDataArray, $chunkSize);
            $chunks                       = \count($data_array);
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
                $res = parent::$dbconn->addDBArray($data);
                // }
            }
            $this->updateNow();
        }
    }

    public function changeDBEntry()
    {
        utminfo(func_get_args());

        foreach ($this->Changed_Array as $video_key => $video_file) {
            parent::$dbconn->video_file = $video_file;
            // parent::$dbconn->video_key  = $video_key;
            $video_name                 = basename($video_file);
            if (! Option::istrue('preview')) {
                parent::$output->writeln('Updateing file from db ' . $video_name);

                parent::$dbconn->UpdateFilePath();
            } else {
                parent::$dbconn->RowBlock->overwrite('Updateing file ' . $video_name . \PHP_EOL);
            }
        }
    }

    public function findRemoved() {}

    public function execUpdate()
    {
        utminfo(func_get_args());

        $date       = null;
        if (! Option::istrue('yes')) {
            $date = $this->lastUpdated();
        }

        $file_array = (new MediaFinder())->search(getcwd(), '/\.mp4$/i', $date);
        if (! \is_array($file_array)) {
            return 0;
        }
        $total      = \count($file_array);
        if ($total > 0) {
            $storagedb           = new StorageDB();
            $storagedb->MultiIDX = \count($file_array);
            foreach ($file_array as $k => $file) {
                $key          = File::getVideoKey($file);
                $data_array[] = $storagedb->updateDBEntry($key, ['video_file' => $file], Option::istrue('all'));
                --$storagedb->MultiIDX;
            }

            $this->updateNow();
        }
    }

    public function getJson()
    {
        utminfo(func_get_args());


        $file_array = Mediatag::$SearchArray;
        foreach ($file_array as $k => $file) {
            $json_key = File::getVideoKey($file);
            if (! str_starts_with($json_key, "x")) {
                $json_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';


                if (! Mediatag::$filesystem->exists($json_file)) {
                    $exec   = new YoutubeExec('');
                    $return = $exec->youtubeGetJson($json_key);


                    if (Mediatag::$filesystem->exists($json_file)) {
                        parent::$output->writeln('<info>adding json ' . basename($return) . ' </info>');
                    } else {
                        parent::$output->writeln('<error>adding fake json for ' . basename($file) . ' </error>');
                        MediaFilesystem::writeFile($json_file, '{"id": "' . $json_key . '"}', false);
                    }
                    //utmdd($file,$json_key);
                } else {
                    parent::$output->writeln('<id>json file for ' . basename($file) . ' exists</id>');
                }


            } else {
                parent::$output->writeln('<comment>skipping ' . basename($file) . ' </comment>');
            }
        }

    }
}
