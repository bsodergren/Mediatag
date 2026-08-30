<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Helper\traits;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Helper\VideoDownloader;
use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\Strings;
use Nette\Utils\FileSystem as nFileSystem;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function dirname;

use const __PLEX_DOWNLOADED__;
use const DIRECTORY_SEPARATOR;
use const FILE_APPEND;
use const PHP_EOL;

trait PatternMethods
{
    public function moveNewJson($json_file)
    {

        // $json_file = $matchedArray[0][$matchedArray[1]];
        $json_file = trim($json_file);
        $success   = preg_match('/-(p?h?[a-z0-9]+).info.json/', basename($json_file), $matches);

        $json_key  =$matches[1];

        if (Mediatag::$filesystem->exists($json_file)) {

            $newJson_file = MediaFile::getjsonFilename(__JSON_CACHE_DIR__, $json_key, 'Update');

            if (Option::istrue('test')) {
                $out = "<question>jSon</question>\n\t<comment>Old:" . basename($json_file) . "</comment>\n\t<info>New:" . basename($newJson_file) . '</info>';
                Mediatag::$output->writeln($out);
            } else {
                //  utmdump([$json_file, $newJson_file]);
                Filesystem::renameFile($json_file, $newJson_file, true);
                Mediatag::$Console->writeln('Moved Completed file <file>' . basename($json_file) . ' to ' . $newJson_file . ' </file>');
                // $this->updatePlaylist( $this->playlist,$json_key);
                if (Option::istrue('print')) {
                    echo 'finisihed';
                }
            }
            // }

            return true;
        }

    }



    public function moveDownloadedVideos($key)
    {
        // Mediatag::$Console->writeln('searching for key ' . $key);
        $file_array = Mediatag::$finder->Search(\__PLEX_DOWNLOAD__, '*' . $key . '*', exit: false);

        if (count($file_array) > 0) {
            foreach ($file_array as $file) {
                if (!file_exists($file)) {
                    continue;
                }

                $currentPath  = dirname($file);
                if (str_ends_with($file, 'mp4')) {
                    $filename = DIRECTORY_SEPARATOR . basename($file, '.mp4');
                } elseif (str_ends_with($file, 'json')) {
                    $filename = DIRECTORY_SEPARATOR . basename($file, '.info.json');
                } else {
                    continue;
                }

                $jsonFile     = $filename . '.info.json';
                $videoFile    = $filename . '.mp4';

                $newPath      = str_replace(\__PLEX_DOWNLOAD__, __PLEX_DOWNLOADED__, $currentPath);
                nFileSystem::createDir($newPath);

                $newVideoFile = $newPath . $videoFile;
                $newJsonFile  = $newPath . $jsonFile;

                nFileSystem::rename($currentPath . $videoFile, $newVideoFile);
                nFileSystem::rename($currentPath . $jsonFile, $newJsonFile);

                Mediatag::$Console->writeln('Moved Completed file <file>' . $videoFile . ' to downloaded </file>');

                if (!Option::istrue('test')) {
                    Filesystem::prunedirs(__PLEX_DOWNLOAD__);
                }
            }
        }
    }

    public function setNumberLines($value)
    {
        $this->num_of_lines = $value;
    }

}
