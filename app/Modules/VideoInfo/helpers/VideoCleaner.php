<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\helpers;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;

trait VideoCleaner
{
    private function cleanMissing($fileSearch, $dbList)
    {
        Mediatag::$log->notice('Method {0}', [__METHOD__]);

        $missing = array_diff($fileSearch, $dbList);

        if (\count($missing) > 0) {
            $this->getMessageLen($missing);
            $fileCount = \count($missing);
            // Mediatag::$output->writeln($this->printNo($fileCount) .' Files in ' . __METHOD__);
            foreach ($missing as $k => $file) {
                $videoFile = $this->thumbToVideo($file);

                if (!file_exists($videoFile)) {
                    $this->renameThumb($file, false);
                    @unlink($file);
                    // Mediatag::$output->writeln('unlink file '.$file);
                    Mediatag::$output->writeln($this->printNo($fileCount--).'<fg=red>Deleting '.$this->setMessage($file).'</>');
                }
            }
        } else {
            // Mediatag::$output->writeln('No Files in ' . __METHOD__);
        }
    }

    private function cleanMissingFile($missing_file)
    {
        Mediatag::$log->notice('Method {0}', [__METHOD__]);

        if (\count($missing_file) > 0) {
            $this->getMessageLen($missing_file);
            $fileCount = \count($missing_file);
            // Mediatag::$output->writeln($this->printNo($fileCount) .' Files in ' . __METHOD__);

            foreach ($missing_file as $k => $file) {
                $query  = 'update '.$this->VideoDataTable.' set '.$this->getTableField().' = null WHERE id = '.$k.'';
                $result = Mediatag::$dbconn->query($query);
                $file   = $this->thumbToVideo($file);
                Mediatag::$output->writeln($this->printNo($fileCount--).'<info>Changing '.$this->setMessage($file).' to null </info>');
            }
        } else {
            // Mediatag::$output->writeln('No Files in ' . __METHOD__);
        }
    }

    private function cleanMissingThumb($missing)
    {
        Mediatag::$log->notice('Method {0}', [__METHOD__]);

        if (\count($missing) > 0) {
            $this->getMessageLen($missing);
            $fileCount = \count($missing);
            // Mediatag::$output->writeln($this->printNo($fileCount) .' Files in ' . __METHOD__);

            foreach ($missing as $k => $file) {
                $query = 'update '.$this->VideoDataTable.' set '.$this->getTableField().' = null WHERE id = '.$k.'';

                $result = Mediatag::$dbconn->query($query);
                $file   = $this->thumbToVideo($file);
                Mediatag::$output->writeln($this->printNo($fileCount--).'<info>Changing '.$this->setMessage($file).' to null </info>');

                if (file_exists($file)) {
                    $fs        = new MediaFile($file);
                    $videoData = $fs->get();
                    $this->get($videoData['video_key'], $file);
                    // Mediatag::$output->writeln($this->returnText); // .'</info>');
                    // } else {
                    // Mediatag::$output->writeln('');
                }
            }
        } else {
            // Mediatag::$output->writeln('No Files in ' . __METHOD__);
        }
    }

    private function doClean($delete = false)
    {
        Mediatag::$log->notice('Method {0}', [__METHOD__]);
        //   $this->doClean('thumbnail',$this->getExistingList(),$res);

        $fileSearch = $this->getPreviewFiles();

if (true === $delete) {
            foreach ($fileSearch as $k => $file) {
                unlink($file);
                unset($fileSearch[$k]);
            }
        }

        [$dbList,
            $missing_file,
            $missing_thumb] = $this->getExistingList();

            // utmdd( [$dbList,
            // $missing_file,
            // $missing_thumb]);

        $this->cleanMissing($fileSearch, $dbList);
        $this->cleanMissingFile($missing_file);
        $this->cleanMissingThumb($missing_thumb);
    }

    private function getPreviewFiles()
    {
        Mediatag::$log->notice('Method {0}', [__METHOD__]);
        $curDir     = str_replace(__PLEX_HOME__.'/'.__LIBRARY__, '', __CURRENT_DIRECTORY__);
        $previewDir = $this->thumbDir.'/'.__LIBRARY__.$curDir;

        (new Filesystem())->mkdir($previewDir);

        $res = Mediatag::$finder->Search($previewDir, '*'.$this->thumbExt);

        if (null === $res) {
            $res = [];
        }

        return $res;
    }

    private function getExistingList()
    {
        Mediatag::$log->notice('Method {0}', [__METHOD__]);

        $missing_thumb = [];
        $missing_mp4   = [];
        $dblist        = [];

        $query  = "SELECT  CONCAT(fullpath,'/',filename) as file_name,id FROM ".$this->VideoDataTable." WHERE Library = '".__LIBRARY__."' AND  ".$this->getTableField()." is not null  AND fullpath like '".__CURRENT_DIRECTORY__."%' ";
        $result = Mediatag::$dbconn->query($query);

        foreach ($result as $_ => $row)
        {
            $thumb = $this->videoToThumb($row['file_name']);

            // utmdump(['video'=> $row['file_name'], 'thumb'=>file_exists($thumb)]);

            if (!file_exists($row['file_name'])) {
                $missing_mp4[$row['id']] = $thumb;

                continue;
            }

            if (!file_exists($thumb)) {
                $missing_thumb[$row['id']] = $row['file_name'];

                continue;
            }
            $dblist[$row['id']] = $thumb;
        }
        //  utmdd([$dblist, $missing_mp4, $missing_thumb]);

        return [$dblist, $missing_mp4, $missing_thumb];
    }

    /**
     * Summary of thumbToVideo.
     *
     * @param string $file
     *
     * @return string
     */
    public function thumbToVideo($file)
    {
        $newFile = str_replace($this->thumbExt, '.mp4', __PLEX_HOME__.str_replace($this->thumbDir, '', $file));
        Mediatag::$log->notice("thumbToVideo \n{0}\n{1}", [basename($file), basename($newFile)]);

        return $newFile;
    }

    public function videoToThumb($file)
    {
        $newFile = str_replace('.mp4', $this->thumbExt, $this->thumbDir.str_replace(__PLEX_HOME__, '', $file));
        Mediatag::$log->notice("videoToThumb \n{0}\n{1}", [basename($file), basename($newFile)]);

        return $newFile;
    }

    public function renameThumb($file, $delete = false)
    {
        Mediatag::$log->notice('Method {0}', [__METHOD__]);

        if (true === $delete) {
            unlink($file);

            return 0;
        }
        $newFile = str_replace('thumbnails', 'backup', $file);
        $path    = \dirname($newFile);

        if (!is_dir($path)) {
            (new SFilesystem())->mkdir($path);
        }

        (new SFilesystem())->rename($file, $newFile, true);
    }
}
