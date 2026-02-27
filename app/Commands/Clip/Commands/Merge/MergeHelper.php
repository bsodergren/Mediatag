<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Merge;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use UTM\Utilities\Option;

use function array_key_exists;

trait MergeHelper
{
    public $cmdOptionMerge = [
        'clips'    => ['cmd' => 'mergeClips', 'desc' => 'Show all transition types'],
        'playlist' => ['cmd' => 'mergePlaylist', 'desc' => 'Show all playlist types'],
    ];

    public function mergeFiles()
    {
        $showCmd = Option::getValue('merge', 1);
        // utmdd($showCmd);
        if (array_key_exists($showCmd, $this->cmdOptionMerge)) {
            $method = $this->cmdOptionMerge[$showCmd]['cmd'];
            $this->$method();

            return 1;
        }

        $this->defaultCmd($this->cmdOptionMerge);

        utmdd($showCmd);
    }

    public function getPlaylistVideosfromId($playlist_id)
    {
        $sql = 'select d.name as name, CONCAT(v.fullpath,\'/\',v.filename) as file_name
        from   ' . __MYSQL_PLAYLIST_DATA__ . ' as d,
        ' . __MYSQL_VIDEO_FILE__ . '  as v,
        ' . __MYSQL_PLAYLIST_VIDEOS__ . ' as p
        where (p.playlist_id = ' . $playlist_id . ' and
        p.playlist_video_id = v.id and
         d.id = p.playlist_id ) ORDER BY v.filename DESC';
        $sql     = str_replace(PHP_EOL, '', $sql);
        $sql     = str_replace('  ', ' ', $sql);
        $results = Mediatag::$dbconn->query($sql);

        return $results;
    }

    public function mergePlaylist()
    {
        $playlist = Option::getValue('playlistid', true);
        $name     = Option::getValue('name', true);

        $filelistArray = $this->getPlaylistVideosfromId($playlist);
        if ($name === null) {
            $name = $filelistArray[0]['name'];
        }
        $ClipName = $this->setClipFilename($name);
        // $this->progress = new MediaIndicator('one');
        foreach ($filelistArray as $file) {
            $filelist[] = $file['file_name'];
        }

        $this->createCompilation($filelist, $ClipName, $name);
    }

    public function mergeClips()
    {
        $this->exec();

        $fileSearch = Option::getValue('search', true);
        $name       = Option::getValue('name', true);

        $directory = $this->getClipDirectory(__CURRENT_DIRECTORY__, 0);

        if ($fileSearch != '') {
            $search = '/.*_(' . $fileSearch . ')_\d+\.mp4/i';
        } else {
            $search = '*.mp4';
        }
        if ($name === null) {
            $name = 'Compilation';
        }
        $file_array = Mediatag::$finder->Search($directory, $search);

        if ($file_array == null) {
            Mediatag::$output->writeln('<comment> No Files Found</>');

            return false;
        }
        $current_file = '';
        $mod          = 0;
        $index        = 0;
        foreach ($file_array as $file) {
            preg_match('/([a-zA-Z0-9-_]+)_([a-zA-Z0-9].*)_([0-9]+)(.mp4)/', $file, $output_array);
            if ($current_file != $output_array[1]) {
                $current_file = $output_array[1];
                $mod += $index;
                $index = 0;
            }
            $idx = $output_array[3] + $mod;

            $fileList[$idx] = $file;
            $index++;
        }
        ksort($fileList);

        // foreach ($fileList as $line) {
        //     $strArray[] = "file '".$line."'";
        // }
        // $string   = implode("\n", $strArray);
        // $listFile = $this->setffmpegFilename($name);
        // Filesystem::write($listFile, $string, 0755);
        $ClipName = $this->setClipFilename($name);
        // $this->progress = new MediaIndicator('one');

        $this->createCompilation($fileList, $ClipName, $name);
    }
}
