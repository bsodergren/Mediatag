<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Chapter;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\Section\Chapters;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Traits\MediaFFmpeg;
use Mhor\MediaInfo\MediaInfo;

use function array_key_exists;
use function count;
use function dirname;

use const PHP_EOL;

trait ChapterHelper
{
    use MediaFFmpeg;

    public $Chapter;

    public $chapterArray;

    public function getChapterList()
    {
        $chapterArray       = [];
        $this->FileIdx      = 0;

        $search             = '';

        foreach ($this->VideoList['file'] as $key => $vidArray) {
            //             $mediaInfo          = new MediaInfo;
            //         $mediaInfoContainer = $mediaInfo->getInfo($vidArray['video_file']);
            //         $videos             = $mediaInfoContainer->getVideos();
            //         $general            = $mediaInfoContainer->getMenus();
            // $keys = [];
            // if(count($general)>0) {
            // foreach($general[1]->list() as $i => $key){
            //     if(\str_starts_with($key,"00")){
            //         $keys[] = $general[1]->get($key);
            //     }
            // }
            // }
            // // utmdd($general);
            //          utmdd($keys);//$general[1]->get('00_00_00000'));

            $this->Chapter = new Chapters();
            $this->Chapter->getvideoId($key);
            if (null !== $this->Chapter->video_id) {
                $query  = $this->Chapter->videoQuery($this->Chapter->video_id, $search);
                $result = Storage::$DB->query($query);
                if (count($result) > 0) {
                    $chapters = $this->getVideoChapters($result);

                    if (null !== $chapters) {
                        if (count($chapters) > 0) {
                            ++$this->FileIdx;

                            $chapterArray[] = $chapters;
                        }
                    }
                }
            }
        }
        $this->chapterArray = $chapterArray;

        return $this->chapterArray;
    }

    public function createChapterFile()
    {
        $this->progress = new MediaIndicator('one');
        foreach ($this->chapterArray as $i => $fileRow) {
            foreach ($fileRow as $K => $FILE) {
                $filename = $FILE['filename'];
                if (!array_key_exists('chapters', $FILE)) {
                    continue;
                }

                if (count($FILE['chapters']) > 0) {
                    $mediaInfo          = new MediaInfo();
                    $mediaInfoContainer = $mediaInfo->getInfo($filename);
                    $chapters           = $mediaInfoContainer->getMenus();

                    $VideoChapters      = 0;

                    if (array_key_exists(0, $chapters)) {
                        foreach ($chapters[0]->list() as $menu) {
                            if (preg_match('/(\d+_\d+_\d+)/', $menu, $output_array)) {
                                ++$VideoChapters;
                            }
                        }
                        if (count($FILE['chapters']) == $VideoChapters) {
                            continue;
                        }
                    }

                    $tags               = (new VideoTags())->get($K, $filename);
                    $chapterFile        = str_replace('.mp4', '_chp.txt', $filename);

                    if (file_exists($chapterFile)) {
                        unlink($chapterFile);
                    }
                    $contents           = [$this->tagFileSection($tags)];

                    foreach ($FILE['chapters'] as $idx => $chapter) {
                        $contents[] = $this->chapterFileSection($chapter);
                    }

                    $fileContents       = implode(PHP_EOL, $contents);

                    MediaFile::file_append_file($chapterFile, $fileContents . PHP_EOL);

                    $this->ffmpegCreateChapterVideo($filename, $chapterFile);

                    if (file_exists($chapterFile)) {
                        unlink($chapterFile);
                    }
                    $file_path          = dirname($filename);
                    $backup_filepath    = str_replace('XXX/', 'XXX/ChapVid/', $file_path);

                    if (!Mediatag::$filesystem->exists($backup_filepath)) {
                        Mediatag::$filesystem->mkdir($backup_filepath);
                    }
                    $backup_filename    = $backup_filepath . '/' . basename($filename);
                    $outputFile         = str_replace('.mp4', '_chapters.mp4', $filename);

                    // utmdd($filename, $backup_filename,$outputFile);
                    Filesystem::renameFile($filename, $backup_filename, true);
                    Filesystem::renameFile($outputFile, $filename);
                }
                // utmdd([$filename, $backup_filename, $outputFile]);
            }
        }
    }

    private function tagFileSection($tag)
    {
        $text = ';FFMETADATA1' . PHP_EOL;

        foreach ($tag as $key => $value) {
            if (null !== $value) {
                if ('studio' == $key) {
                    $key = 'album';
                }
                $text .= $key . '=' . $value . PHP_EOL;
            }
        }

        return trim($text);
    }

    private function chapterFileSection($chapter)
    {
        $text = '[CHAPTER]' . PHP_EOL;
        $text .= 'TIMEBASE=1/1' . PHP_EOL;
        $text .= 'START=' . $chapter['start'] . PHP_EOL;
        $text .= 'END=' . $chapter['end'] . PHP_EOL;
        $text .= 'title=' . trim(str_replace('Chapter', '', str_replace('_', ' ', $chapter['text'])));

        return $text;
    }

    // private function createChapterVideo($chapter,$file)
    // {

    //     ffmpeg -y -i MyWifesFirstBlowBang4-Scene2_s02_ChadAlva_CodeySteele_1080p_h264.mp4 -i MyWifesFirstBlowBang4-Scene2_s02_ChadAlva_CodeySteele_1080p_h264_chp.txt  -map_metadata 1 -c copy output3.mp4

    // }
}
