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
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Traits\MediaFFmpeg;
use Mhor\MediaInfo\MediaInfo;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function dirname;

use const PHP_EOL;

trait ChapterHelper
{
    use MediaFFmpeg;

    public $videoInfo;
    public $chapterArray;

    private $FileIdx = 0;

    public function getChapterList()
    {
        $chapterArray         = [];


        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $chapterArray[] = $this->getVideoChapter($vidArray);
        }

        $this->chapterArray   = $chapterArray;

        return $this->chapterArray;
    }

    private function getVideoChapter($videoInfo)
    {
        $search             = '';
        $key                = $videoInfo['video_key'];

        if (Option::isTrue('markers')) {
            $this->videoInfo        = new Markers();
            $textField              = 'markerText';
        } else {
            $this->videoInfo        = new Chapters();
            $textField              = 'chapterText';
        }

        $video_id           = $this->videoInfo->getvideoId($key);

        if (null !== $video_id) {
            $query    = $this->videoInfo->videoQuery($video_id, $search);
            $result   = Storage::$DB->query($query);
            if (count($result) > 0) {
                $chapters = $this->getVideoChapters($result, $textField);
                if (null !== $chapters) {
                    if (count($chapters) > 0) {
                        ++$this->FileIdx;
                        return $chapters;
                    }
                }

            }
        }
        Mediatag::$output->writeln('<comment>' . basename($videoInfo['video_name']) . '</comment> <info>has no markers or chapters</info>');

        return null;

    }

    public function createChapterFile()
    {
        $this->progress = new MediaIndicator('one');
        foreach ($this->chapterArray as $i => $fileRow) {

            if (is_null($fileRow)) {

                continue;
            }

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
                            Mediatag::$output->writeln('<comment>' . basename($filename) . '</comment> <info>Already has chapters</info>');
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
                    // Mediatag::$output->writeln('<comment>' . basename($filename) . '</comment> <info>Adding Chapters</info>');

                    $this->createChapterVideo($filename, $chapterFile);
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

    private function createChapterVideo($filename, $chapterFile)
    {
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


    public function getVideoChapters($videoInfo, $textField)
    {

        $videoKey    = 0;
        $chapterRow  = [];
        $chapters    = [];
        $chapterPos  = [];
        $chapterIdx  = 0;
        $rowIdx      = 0;
        foreach ($videoInfo as $k => $row) {
            if ($k == 0) {
                // utmdd($row);
            }
            if (! array_key_exists('timeCode', $row)) {
                return null;
            }

            if ($row['video_key'] != $videoKey) {
                $videoKey   = $row['video_key'];
                $chapterIdx = 0;
            }

            $chapters[$row['video_key']]             = [
                'filename' => $row['filename'],
            ];

            if ($chapterIdx == 0) {
                $chapterRow[] = [
                    'start' => 0,
                    'end'   => $videoInfo[$rowIdx + 1]['timeCode'] - 1,
                    'text'  => $row[$textField],
                ];
                $chapterIdx++;
                $rowIdx++;

                continue;
            }
            $chapterRow[$chapterIdx]['start']        = (int) $row['timeCode'];

            if (array_key_exists($rowIdx + 1, $videoInfo)) {
                $chapterRow[$chapterIdx]['end'] = $videoInfo[$rowIdx + 1]['timeCode'] - 1;
            } else {
                $chapterRow[$chapterIdx]['end'] = $row['duration'] / 1000;
            }

            $chapterRow[$chapterIdx]['text']         = $row[$textField];

            $chapters[$row['video_key']]['chapters'] = $chapterRow;
            $chapterIdx++;
            $rowIdx++;
        }

        return $chapters;
    }
}
