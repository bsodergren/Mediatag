<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Chapter;

use const PHP_EOL;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Traits\MediaFFmpeg;
use Mhor\MediaInfo\MediaInfo;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function dirname;

trait ChapterHelper
{
    use MediaFFmpeg;

    public $Marker;

    public $markerArray;

    public function getMarkerList()
    {
        $markerArray   = [];
        $this->FileIdx = 0;

        $search = Option::getValue('clip', true);

        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $this->Marker = new Markers;

            $this->Marker->getvideoId($key);
            // utmdump($this->Marker->video_id);
            if ($this->Marker->video_id !== null) {
                $query  = $this->Marker->videoQuery($this->Marker->video_id, $search);
                $result = Mediatag::$dbconn->query($query);
                // // utmdump($result);
                if (count($result) > 0) {
                    $markers = $this->getVideoChapters($result);
                    if ($markers !== null) {
                        if (count($markers) > 0) {
                            $this->FileIdx++;

                            $markerArray[] = $markers;
                        }
                    }
                }
            }
        }
        $this->markerArray = $markerArray;

        return $this->markerArray;
    }

    public function createChapterFile()
    {
        $this->progress = new MediaIndicator('one');

        foreach ($this->markerArray as $i => $fileRow) {
            foreach ($fileRow as $K => $FILE) {
                $filename = $FILE['filename'];
                // // utmdump($FILE);
                if (! array_key_exists('markers', $FILE)) {
                    continue;
                }

                if (count($FILE['markers']) > 0) {
                    $mediaInfo          = new MediaInfo;
                    $mediaInfoContainer = $mediaInfo->getInfo($filename);
                    $chapters           = $mediaInfoContainer->getMenus();

                    $VideoChapters = 0;

                    if (array_key_exists(0, $chapters)) {
                        foreach ($chapters[0]->list() as $menu) {
                            if (preg_match('/(\d+_\d+_\d+)/', $menu, $output_array)) {
                                $VideoChapters++;
                            }
                        }
                        if (count($FILE['markers']) == $VideoChapters) {
                            continue;
                        }
                    }

                    $tags        = (new VideoTags)->get($K, $filename);
                    $chapterFile = str_replace('.mp4', '_chp.txt', $filename);
                    if (file_exists($chapterFile)) {
                        unlink($chapterFile);
                    }
                    $contents = [$this->tagFileSection($tags)];
                    foreach ($FILE['markers'] as $idx => $marker) {
                        $contents[] = $this->chapterFileSection($marker);
                    }
                    $fileContents = implode(PHP_EOL, $contents);
                    MediaFile::file_append_file($chapterFile, $fileContents . PHP_EOL);

                    $this->ffmpegCreateChapterVideo($filename, $chapterFile);

                    if (file_exists($chapterFile)) {
                        unlink($chapterFile);
                    }
                    $file_path       = dirname($filename);
                    $backup_filepath = str_replace('XXX/', 'XXX/ChapVid/', $file_path);

                    if (! Mediatag::$filesystem->exists($backup_filepath)) {
                        Mediatag::$filesystem->mkdir($backup_filepath);
                    }
                    $backup_filename = $backup_filepath . '/' . basename($filename);
                    $outputFile      = str_replace('.mp4', '_chapters.mp4', $filename);

                    Filesystem::renameFile($filename, $backup_filename);
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
            if ($value !== null) {
                if ($key == 'studio') {
                    $key = 'album';
                }
                $text .= $key . '=' . $value . PHP_EOL;
            }
        }

        return trim($text);
    }

    private function chapterFileSection($marker)
    {
        $text = '[CHAPTER]' . PHP_EOL;
        $text .= 'TIMEBASE=1/1' . PHP_EOL;
        $text .= 'START=' . $marker['start'] . PHP_EOL;
        $text .= 'END=' . $marker['end'] . PHP_EOL;
        $text .= 'title=' . trim(str_replace('Chapter', '', str_replace('_', ' ', $marker['text'])));

        return $text;
    }

    // private function createChapterVideo($chapter,$file)
    // {

    //     ffmpeg -y -i MyWifesFirstBlowBang4-Scene2_s02_ChadAlva_CodeySteele_1080p_h264.mp4 -i MyWifesFirstBlowBang4-Scene2_s02_ChadAlva_CodeySteele_1080p_h264_chp.txt  -map_metadata 1 -c copy output3.mp4

    // }
}
