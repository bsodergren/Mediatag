<?php

namespace Mediatag\Commands\Test\HelperCmd\traits;


use FFMpeg\FFMpeg;
use Symfony\Component\Process\Process;
use Exception;
use Mediatag\Bundle\Dialog\Options\Common;
use Mediatag\Bundle\Dialog\Widgets\Buildlist;
use Mediatag\Bundle\Dialog\Widgets\Gauge;
use Mediatag\Bundle\Dialog\Widgets\Menu;
use Mediatag\Bundle\WhipTail\Controller as WhipTail;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\Traits\ScriptWriterHelper;
use Mediatag\Modules\Metatags\MetaTagInfo;
use Mediatag\Modules\TagBuilder\Json\Reader;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\TagBuilder\VideoPattern;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Traits\MediaFFmpeg;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder as NetteFinder;
use Nette\Utils\Strings;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;
use UTMDbLib\Metatags\Artist;
use UTMDbLib\VideoInfo\VideoInfo as LibVinfo;
use Nette\Utils\FileSystem as NetteFile;
trait BackupVideos
{

     private $i = 50;

    private $backupDrive = '/mnt/sas2';


    private  $StudioList = "/media/Videos/Plex/XXX/studio_files.txt";

    private static $StudioListText = '';

    private function findExistinFileMap($filename)
    {
            

        // utmdd($filename);
            foreach ( $this->StudioListText  as $lineNum => $exclude) {
                $filenameLen = strlen($filename);
                $fileFirstPart = substr($filename, 0, $filenameLen/2);
                if (str_contains( strtolower($exclude),strtolower($fileFirstPart))) {
                    return $exclude;
                }
            }
            return false;
    }

    public function SortStudios()
    {
        
    $StudioListText = NetteFile::readLines($this->StudioList, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ;

        
            $this->StudioListText = iterator_to_array($StudioListText);

        $sourceDir = '/media/Videos/Plex/XXX/Sort';

        $finder = new Finder();
        $finder->files()->in($sourceDir)->sortByCaseInsensitiveName(true);

        foreach ($finder as $file) {
            $sourcePath = dirname($file->getPath(),1);
            $fileName = $file->getFilename();
            $found = $this->findExistinFileMap($fileName);


            if($found !== false){
            $sourceFile = $file->getRealPath();

            $destination = FileSystem::joinPaths($sourcePath, 'Studios', $found);
                $destinationDir = basename($destination);
                
                //FileSystem::createDir($destinationDir);

                Mediatag::$Console->writeln('<info>Old File <file>' . $sourceFile . '</file> </info>');

              Mediatag::$Console->writeln('<info>New File <file>' . $destination . '</file> </info>');

                continue;
            }
            // $firstDir = explode(\DIRECTORY_SEPARATOR, $relativePath);
            // $currentDir = $firstDir[0];

            // 

            // // Create the destination directory if it doesn't exist
            // FileSystem::createDir($destinationDir . '/' . $currentDir);

            // // Move the file to the destination directory
            // FileSystem::rename($sourcePath, $destinationDir . '/' . $relativePath);
        }
        return false;
    }

    public function restoreStudios()
    {

        if (Option::isTrue('limit')) {
            $this->i = (int) Option::getValue('limit');
        }

        if (Option::isTrue('source')) {
            $this->backupDrive = Option::getValue('source');
        }
        // $backupDrive = '/mnt/sas2';
        $sourceDir = $this->backupDrive . '/Studios';
        Mediatag::$Console->writeln('<info>restoring <file>' . $this->backupDrive . '</file> directory</info>');

        // utmdd($this->backupDrive);
        $destinationDir = '/media/Videos/Plex/XXX/Studios';

        $CopiedDestDir = $this->backupDrive . '/Copied/Studios';

        $finder = new Finder();
        $finder->files()->in($sourceDir)->sortByCaseInsensitiveName(true);

        $filter = function (\SplFileInfo $file) {
            $search = Option::getValue('filter');
            if (! str_starts_with($file->getRelativePathname(), $search)) {
                // utmdd([$file->getRelativePathname(),$search]);
                return false;
            }

        };
        if (Option::isTrue('filter')) {
            $finder->filter($filter);
        }


        $excludeFilter = function (\SplFileInfo $file) {
            $text  = NetteFile::readLines(self::$BAD_FILE, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
            foreach ($text as $lineNum => $exclude) {
                if (str_contains(strtolower($file->getRelativePathname()), strtolower($exclude))) {
                    utmdump(['Excluding file', $file->getRelativePathname(), $exclude]);

                    return false;
                }
            }
        };
        $finder->filter($excludeFilter);


        $currentDir = '';
        $filesArray = iterator_to_array($finder, false);
        $resultCount = \count($filesArray);
        if ($this->i > $resultCount) {
            $this->i = $resultCount;
        }

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $sourcePath = $file->getRealPath();
            $firstDir = explode(\DIRECTORY_SEPARATOR, $relativePath);
            if ($currentDir !== $firstDir[0]) {
                if ($currentDir !== '') {
                    Mediatag::$Console->writeln('<info>Moving <file>' . $currentDir . '</file> directory</info>');

                    FileSystem::createDir($CopiedDestDir);

                    FileSystem::rename($sourceDir . '/' . $currentDir, $CopiedDestDir . '/' . $currentDir);
                    // utmdd("");
                }
                $currentDir = $firstDir[0];
                Mediatag::$Console->writeln('<info>Checking <file>' . $currentDir . '</file> directory</info>');
            }

            $destinationPath = $destinationDir . \DIRECTORY_SEPARATOR . $relativePath;

            if ($this->copyWithProgress($sourcePath, $destinationPath)) {
                $this->i--;
                //    Mediatag::$Console->writeln("Failed to copy: $sourcePath");
            }

            if ($this->i == 0) {
                Mediatag::$Console->writeln('More than 50 files copied.');
                break;

            }
        }
        // The last directory has no following directory to trigger its move.
        // Leave it in place if the limit stopped processing partway through it.
        if ($currentDir !== '' && $this->i > 0 && is_dir($sourceDir . '/' . $currentDir)) {
            Mediatag::$Console->writeln('<info>Moving <file>' . $currentDir . '</file> directory</info>');
            FileSystem::createDir($CopiedDestDir);
            FileSystem::rename($sourceDir . '/' . $currentDir, $CopiedDestDir . '/' . $currentDir);
        }


    }
    public function backupVideos()
    {
        $db  = MysqliDb::getInstance();
        $res = $db->rawQuery($this->DbQuery);
        foreach ($res as $k => $v) {
            $videoId = $v['id'];
            if( $artist = self::getArtists($videoId)){
                // $res[$k]['artist'] = $artist;
            }
        }
    }
}
