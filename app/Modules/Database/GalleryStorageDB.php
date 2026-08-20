<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\Gallery;
use Mediatag\Modules\VideoInfo\Section\preview\GifPreviewFiles;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mhor\MediaInfo\Attribute\Duration;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;

use function count;
use function is_array;

use const PHP_EOL;

class GalleryStorageDB extends StorageDB
{
    public function __construct(?MysqliDb $DbConnection = null)
    {
        if (null === $DbConnection) {
            $db           = MysqliDb::getInstance();

            $DbConnection = $db;
        }

        $this->mysqllib = $DbConnection; // ->getInstance();
        $this->mysqllib->setTrace(true);
        self::$DB       = $this;

        //  utmdd($this->mysqllib,self::$DB);

        if (null !== Mediatag::$output) {
            $this->output      = Mediatag::$output;
            $this->input       = Mediatag::$input;
            $this->FileNumber  = $this->output->section();
            $this->headerBlock = $this->output->section();
            $this->RowBlock    = $this->output->section();
        }
    }

    public function createDbEntry($video_file, $video_key)
    {
        // utminfo(func_get_args());

        $this->init($video_file);

        $data          = [
            'video_key' => $video_key,
            'filename'  => $this->video_name,
            'fullpath'  => $this->video_path,
            'Library'   => __LIBRARY__,
            // 'filesize'  => filesize($video_file),
        ];

        $data['added'] = self::$DB->mysqllib->now();

        return $data;
    }

    public function updateDBEntry($key, $videoData, $all = true)
    {
        // utminfo(func_get_args());

        $video_file                            = $videoData['video_file'];
        $video_id                              = true;
        $exists                                = $this->videoExists($key);
        Mediatag::$Display->BlockInfo          = ['No' => '<info>' . $this->MultiIDX . '</info>'];
        $videoBlockInfo                        = null;
        $action                                = '<comment>Updated</comment> ';

        if (null === $exists) {
            $data_array = $this->createDbEntry($video_file, $key);
            $video_id   = $this->insert($data_array);
            if (null !== $video_id) {
                $query  = 'insert into ' . __MYSQL_VIDEO_SEQUENCE__ . ' (seq_id,video_id,video_key,Library) values ';
                $query .= " (nextseq('" . __LIBRARY__ . "')," . $video_id . ",'" . $key . "','" . __LIBRARY__ . "')";
                $this->query($query);

                $action = '<comment>Added</comment> ';
            } else {
                $action = '<error>Duplicate</error> ';
            }
        }

        Mediatag::$Display->BlockInfo['Video'] = $action . basename($video_file) . ' ';
        if (null !== $video_id) {
            // $this->vtags = new VideoTags();
            Mediatag::$Display->BlockInfo['MetaTags'] = (new Gallery())->getVideoInfo($key, $video_file);
            // $this->vinfo = new VideoInfo();
            //
            // if (true === $all) {

            //     // $this->thumb = new Thumbnail();
            //     Mediatag::$Display->BlockInfo['thumbnail'] = (new Thumbnail())->getVideoInfo($key, $video_file);

            //     Mediatag::$Display->BlockInfo['VideoInfo'] = (new VideoInfo())->getVideoInfo($key, $video_file);

            //     // $this->duration = new Duration();
            //     Mediatag::$Display->BlockInfo['Duration']  = (new Duration())->getVideoInfo($key, $video_file);

            //     // $this->preview = new GifPreviewFiles();
            //     Mediatag::$Display->BlockInfo['Preview']   = (new GifPreviewFiles())->getVideoInfo($key, $video_file);

            // }
        }

        foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
            $value            = trim($value);

            $videoBlockInfo[] = Mediatag::$Display->formatTagLine($tag, $value, 'fg=yellow');
        }
        if (is_array($videoBlockInfo)) {
            $videoBlockInfo = Mediatag::$Display->sortBlocks($videoBlockInfo);
            Mediatag::$Display->VideoInfoSection->writeln($videoBlockInfo);
            //  Mediatag::$Display->VideoInfoSection->writeln("");
        }
    }

    public function addDBArray($data)
    {
        // utminfo(func_get_args());

        $this->video_string           = [];
        $vdata                        = [];
        Mediatag::$Display->BlockInfo = [];
        // $this->MultiIDX               = 1;
        $total                        = count($data);
        // utmdd($this->MultiIDX );
        foreach ($data as $k => $row) {
            // $VideoQuery[$row['video_key']][__MYSQL_VIDEO_FILE__] = $row;
            $vdata = ['video_file' => $row['fullpath'] . '/' . $row['filename']];

            $this->updateDBEntry($row['video_key'], $vdata, Option::istrue('all'));
            if (null !== $this->progressbar) {
                $this->progressbar->advance();
            }
            if (null !== $this->progressbar1) {
                $this->progressbar1->advance();
            }

            //            $this->video_string[] = '<info>'.$this->MultiIDX.'</info> : Video <comment>'.$row['filename'].'</comment> added to db ';
            --$this->MultiIDX;
        }
        $this->video_string[]         = ' ' . PHP_EOL;
        //   $this->RowBlock->overwrite($this->video_string);
    }
}
