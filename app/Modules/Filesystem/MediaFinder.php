<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Filesystem;

use Mediatag\Core\Mediatag;
use Mediatag\Core\MediaCache;
use Mediatag\Traits\Callables;
use UTM\Utilities\Option;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\ScriptWriter;
use UTM\Bundle\Monolog\UTMLog;
use Symfony\Component\Finder\Finder as SFinder;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Utilities\Strings as UtilitiesStrings;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;

/**
 * Summary of Finder.
 */
class MediaFinder extends SFinder
{
    use Callables;

    /**
     * Summary of video_file.
     */
    public $video_file;

    /**
     * Summary of video_name.
     */
    public $video_name;

    /**
     * duplicateFiles.
     *
     * @var array
     */
    public $duplicateFiles = [];

    /**
     * Summary of video.
     *
     * @var array
     */
    public $video = [];

    /**
     * Summary of output.
     */
    public $output;

    /**
     * Summary of finder.
     */
    public $finder;

    public $excludeDir;

    /**
     * Summary of video_library.
     */
    public $video_library;

    /**
     * Summary of file_path.
     */
    private $file_path;

    /**
     * Summary of filename.
     */
    private $filename;

    /**
     * Summary of full_path.
     */
    private $full_path;

    /**
     * Summary of studio_name.
     */
    private $studio_name;

    /**
     * Summary of full_filename.
     */
    private $full_filename;

    public $defaultCmd;

    /**
     * renameCommaFiles.
     */
    public function renameCommaFiles($filelist, $spaces = false): array
    {
        $first_part = '';
        $rename_file = false;
        $oldName = '';

        foreach ($filelist as $i => $fileRow) {
            if (str_contains($fileRow, ',')) {
                $oldName = $fileRow;
                $newName = str_replace(',', '', $fileRow);
                $rename_file = true;
            } else {
                if (str_contains($fileRow, '.mp4')) {
                    $oldName = $fileRow;
                    $newName = $first_part.$fileRow;

                    if (true === $rename_file) {
                        $oldName = $first_part.','.$fileRow;
                    }
                    $first_part = '';
                } else {
                    $first_part = $fileRow;
                    $rename_file = true;

                    continue;
                }
            }

            if (true === $spaces) {
                $pathInfo = pathinfo($newName);
                $newName = $pathInfo['basename'];

                $newName = UtilitiesStrings::cleanFileName($newName);
                $newName = str_replace('__', '_', $newName);
                $newName = str_replace('_.', '.', $newName);

                if ('.' != $pathInfo['dirname']) {
                    $newName = $pathInfo['dirname'].'/'.$newName;
                }

                if ($oldName != $newName) {
                    $rename_file = true;
                }
            }
            if (true === $rename_file) {
                if (file_exists($newName)) {
                    $newName = str_replace('.mp4', '_1.mp4', $newName);
                    if (file_exists($newName)) {
                        $newName = str_replace('_1.mp4', '_2.mp4', $newName);
                    }
                }

                $message = 'Renaming file from <comment>'.basename($oldName).'</comment> to';
                $message2 = '                -> <comment>'.basename($newName).'</comment> ';
                UTMLog::Logger('Renaming file from ', $oldName);
                UTMLog::Logger('Renaming file to ', $newName);
                $this->output->writeln('<info>'.$message.'</info>');
                $this->output->writeln('<info>'.$message2.'</info>');
                //  Filesystem::renameFile($oldName, $newName);
            }

            $newFileArray[] = $newName;

            $first_part = '';
            $rename_file = false;
        }

        return $newFileArray;
    }

    /**
     * Summary of ExecuteSearch.
     */
    public function ExecuteSearch(): array
    {
        $FileArray = [];
        UTMLog::logger('Search');

        if (Option::isTrue('filelist')) {
            $file_array = $this->getFilelistOption();
        } else {
            $file_array = $this->searchFiles();
        }

        if (\is_array($file_array)) {
            if (Option::isTrue('filenumber')) {
                $FileArray = $this->getFileNumberArray($file_array);
            } elseif (Option::isTrue('range') || Option::isTrue('max')) {
                $FileArray = $this->getRangeArray($file_array);
            } else {
                $FileArray = $file_array;
            }

            return $FileArray;
        }

        return [];
    }

    public function getRangeIds($total, $offset = 1)
    {
        $start = 0;

        if (Option::isTrue('range')) {
            $range = Option::getValue('range');
            if (str_contains($range[0], '-')) {
                $split = $range[0];
                $range = explode('-', $split);
            }
            $start = $range[0] - $offset;

            if (\array_key_exists('1', $range)) {
                if ($range[1] < $total) {
                    $total = (int) $range[1];
                }
            }
        }
        if (Option::isTrue('max')) {
            $total = (int) Option::getValue('max') + $start;
        }

        return [$total, $start];
    }

    public function getRangeArray($file_array): array
    {
        $start = 0;
        $total = \count($file_array);

        [$total,$start] = $this->getRangeIds($total);
        for ($q = $start; $q < $total; ++$q) {
            $file_name = $file_array[$q];

            $video_key = File::file($file_name, 'videokey');
            $video_file = File::file($file_name, 'fullname');
            $FileArray[$video_key] = $video_file;
        }

        return $FileArray;
    }

    public function getFileNumberArray($file_array): array
    {
        $start = 0;
        $total = \count($file_array);
        $FileArray = [];
        $filenumber = Option::getValue('filenumber');
        if (str_contains($filenumber, ',')) {
            $range = explode(',', $filenumber);
            foreach ($range as $q) {
                if ($q > $total) {
                    continue;
                }
                $file_name = $file_array[$q - 1];
                $video_key = MediaFile::file($file_name, 'videokey');
                $FileArray[$video_key] = $file_name;
            }
        } else {
            if (str_contains($filenumber, '-')) {
                $range = explode('-', $filenumber);
                $start = $range[0] - 1;
                $stop = $range[1];
            } else {
                $start = (Option::getValue('filenumber') - 1);
                $stop = $start + 1;
            }

            for ($q = $start; $q < $stop; ++$q) {
                if ($q >= $total) {
                    continue;
                }
                $file_name = $file_array[$q];
                $video_key = MediaFile::file($file_name, 'videokey');
                $FileArray[$video_key] = $file_name;
            }
        }

        return $FileArray;
    }

    /**
     * Summary of Search.
     *
     * @return array|null
     */
    public function Search($path, $search, $date = null)
    {
        return $this->searchFiles($search, $path, $date);
    }

    /**
     * Summary of find.
     *
     * @return array|null
     */
    public static function find($file, $location)
    {
        return (new self())->searchFiles($file, $location);
    }

    /**
     * Summary of searchFiles.
     *
     * @param mixed|null $path
     *
     * @return array|null
     */
    protected function searchFiles($search = '/\.mp4$/i', $path = null, $date = null)
    {
        if (null === $path) {
            $path = getcwd();
        }



        UTMLog::logger('Search Directory', $path);

        $finder = new SFinder();
        $filesystem = new SFilesystem();

        $finder->files()->in($path);
        if($date !== null) {
            $finder->date('>= '. $date);
        }
        if (null !== $this->excludeDir) {

            $finder->exclude($this->excludeDir);
        }
        // else {
        //     $finder->files()->in($path)->name($search)->sortByCaseInsensitiveName();
        // }

        $finder->name($search)->sortByCaseInsensitiveName();
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $video_file = $file->getRealPath();
                if (str_contains($video_file, '-temp-')) {
                    $filesystem->remove($video_file);

                    continue;
                }
                $file_array[] = $video_file;
            }


            $file_array = $this->onlyNew($path, $file_array);
            
            if(is_array($file_array)) {
                if(count($file_array) > 0) {                
                    if (Option::isTrue('dump')) {
                        $this->scriptNewFiles($file_array);
                        return null;
                    }
                    return $file_array;
                }
            }
        }
//dump($file_array);

        //if (isset($this->output)) {
            Mediatag::$output->writeln('<info>No files found</info>');
        //}

  //      return null;
    }

    /**
     * Summary of getFilelistOption.
     *
     * @return array|bool|float|int|string|null
     */
    private function getFilelistOption()
    {
        if (Option::isTrue('filelist')) {
            return Option::getValue('filelist');
        }

        return null;
    }

    public function onlyNew($path, $fileArray)
    {
        $cache_id = md5($path) . "_".__SCRIPT_NAME__;
        $cache_array = null;
        $Changed_Array = [];
        $New_Array = [];

        if(__SCRIPT_NAME__ == 'mediadb') {
            return $fileArray;
        }

        if (Option::isTrue('cache')) {
            MediaCache::put($cache_id, $fileArray);
            $cache_array = MediaCache::get($cache_id);
            Mediatag::$output->writeln('Wrote new cache with '. count($cache_array). ' files');
             
            return null;
        }

        
        if (Option::isTrue('new')) {
            $cache_array = MediaCache::get($cache_id);
        } else {
            MediaCache::put($cache_id, $fileArray);
            return $fileArray;
        }

        $DEBUG['cache_id'] = $cache_id;
        $DEBUG['cache_array'] = $cache_array;


        if(is_array($cache_array)) {
            $Deleted_Array = MediaArray::diff($cache_array, $fileArray, false);
            $New_Array = MediaArray::diff($fileArray, $cache_array, false);
            foreach ($fileArray as $key => $file) {

                if (MediaArray::Search($Deleted_Array, basename($file))) {
                    $Changed_Array[] = $file;
                    unset($New_Array[$key]);
                    unset($Deleted_Array[$key]);

                }

            }           
        }

        MediaCache::put($cache_id, $fileArray);

        if(count($New_Array) > 0) {
            return $New_Array;
        } else {
            return null;
        }

    }



    public function scriptNewFiles($file_array)
    {
        if (\count($file_array) > 0) {
            $obj = new ScriptWriter('newfiles.sh', __CURRENT_DIRECTORY__);
            $obj->addCmd('update', ['-U', '-f'],true,true);
        
            foreach ($file_array as $i => $missing_file) {
                
                    $obj->addFile($missing_file, false);
                

            }
            $obj->write();
            Mediatag::$output->writeln('Wrote new script');
        }
    }
}
