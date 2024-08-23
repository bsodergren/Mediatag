<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Filesystem;

use Mediatag\Traits\Callables;
use Mediatag\Utilities\MediaArray;
use Nette\Utils\Callback;
use Nette\Utils\FileSystem as NetteFile;
use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;
use Symfony\Component\Finder\Finder as SFinder;
use Symfony\Component\Process\Process as ExecProcess;

/**
 * Filesystem.
 */
class MediaFilesystem extends SFilesystem
{
    use Callables;

    public static $clean_up = 3;

    private static $tempdir = '.bak';

    public static function __callStatic($method, $params)
    {
        $methodArray = get_class_methods(NetteFile::class);
        if (null !== MediaArray::search($methodArray, $method)) {
            $handler = ['Nette\Utils\FileSystem', $method];

            return \call_user_func_array($handler, $params);
        }
    }

    /**
     * getRelative.
     *
     * @param mixed $path
     */
    public static function getRelative(string $path): string
    {
        $new_path = (new SFilesystem())->makePathRelative($path, __CURRENT_DIRECTORY__);

        return rtrim($new_path, '/');
    }

    /**
     * writeFile.
     */
    public static function writeFile($file, $content, $backup = true)
    {
        if (!file_exists($file)) {
            touch($file);
        }

        if (\is_array($content)) {
            $content_string = implode("\n", $content);
        } else {
            $content_string = $content;
        }
        if (true === $backup) {
            self::backupPlaylist($file);
        }

        file_put_contents($file, $content_string.\PHP_EOL);
    }

    public static function writePlaylist($file, $content)
    {
        $lineArray = [];
        foreach ($content as $line) {
            if (str_contains($line, '&')) {
                $lineArray[] = Strings::before($line, '&');
            } else {
                $lineArray[] = $line;
            }
        }
        if (\count($lineArray) > 0) {
            self::writeFile($file, $lineArray);
        }
    }

    /**
     * writeArray.
     *
     * @param mixed $content
     */
    public static function writeArray($file, $arrayName, array $content)
    {
        foreach ($content as $i => $line) {
            $studio          = $line;
            $key             = $line;
            if (str_contains($line, ':')) {
                $key    = Strings::before($line, ':');
                $studio = Strings::after($line, ':');
            }
            $fileArray[$key] = $studio;
        }

        //        utmdd([__METHOD__,$fileArray]);

        self::backupPlaylist($file);
        file_put_contents($file, '<?php $'.$arrayName.' = '.var_export($fileArray, true).';');
    }

    public static function backupPlaylist($filename, $directory = false, $move = false)
    {
        if (!file_exists($filename)) {
            return 0;
        }

        $filesystem    = new SFilesystem();
        $file          = realpath($filename);
        $filename      = basename($file);
        $fileNameNoExt = Strings::before($filename, '.', 1);

        if (false === $directory) {
            $directory = \dirname($file);
            $directory = $directory.'/'.self::$tempdir.'/'.$fileNameNoExt;
        }

        if (!is_dir($directory)) {
            $filesystem->mkdir($directory);
        }

        $backupFile    = $directory.'/'.$filename;

        $ext           = '';

        if ('old' == Strings::after($backupFile, '.', -1)) {
            $fileNo = 1;
        }
        if (is_numeric(Strings::after($backupFile, '.', -1))) {
            $number = Strings::after($backupFile, '.', -1);
            $fileNo = $number + 1;
        }

        if (isset($fileNo)) {
            if (self::$clean_up > 0) {
                if (self::$clean_up < $fileNo) {
                    $r      = MediaFinder::find($filename.'*', $directory);
                    rsort($r);
                    unlink($r[0]);
                    foreach ($r as $idx => $rfile) {
                        if (\array_key_exists($idx + 1, $r)) {
                            (new SFileSystem())->rename($r[$idx + 1], $r[$idx]);
                        }
                    }
                    $fileNo = '';
                } else {
                    $ext = '.'.$fileNo;
                }
            } else {
                $ext = '.'.$fileNo;
            }
        }

        $backup_name1  = $fileNameNoExt.'.old'.$ext;

        $backup_name   = $directory.'/'.basename($backup_name1);

        if (file_exists($backup_name)) {
            self::backupPlaylist($backup_name, $directory, $move);
        }

        if (true == $move) {
            (new SFileSystem())->rename($file, str_replace('.old', '.txt', $backup_name));
        } else {
            (new SFileSystem())->copy($file, $backup_name);
        }

        // return $backup_name;
    }

    /**
     * RenameDir.
     */
    public static function RenameDir($old, $new)
    {
        $file_array = [];
        $finder     = new SFinder();
        $filesystem = new SFilesystem();

        if (is_dir($new)) {
            $finder->files()->in($old)->sortByName();
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $old_file = $file->getRealPath();
                    $newpath  = $new.str_replace($old, '', $file->getPath());
                    if (!is_dir($newpath)) {
                        $filesystem->mkdir($newpath);
                    }
                    $new_file = $newpath.'/'.$file->getBasename();

                    $oldFile  = self::getRelative($old_file);
                    $newFile  = self::getRelative($new_file);

                    echo "Renaming {$oldFile} to {$newFile}".\PHP_EOL;
                    NetteFile::rename($old_file, $new_file);
                }
            } else {
                NetteFile::delete($old);
                echo "No files in {$old} ".\PHP_EOL;
            }
        } else {
            $oldFile = self::getRelative($old);
            $newFile = self::getRelative($new);
            echo "Renaming {$oldFile} to {$newFile}".\PHP_EOL;

            NetteFile::rename($old, $new);
        }
    }

    public static function readLines($file, $callback = false)
    {
        if (!file_exists($file)) {
            return false;
        }

        if (\is_string($callback)) {
            $n        = new self();
            $callback = Callback::check([$n, $callback]);
        }

        if (\is_array($callback)) {
            $callback = Callback::check($callback);
        }
        // if(is_callable($callback)){

        // }

        $text  = NetteFile::readLines($file);

        $array = [];

        foreach ($text as $lineNum => $line) {
            if ('' != $line) {
                if (true == $callback) {
                    $res = $callback($line);
                    if (false !== $res) {
                        if (\is_array($res)) {
                            $array = array_merge($array, $res);
                        } else {
                            $array[] = $res;
                        }
                    }
                } else {
                    $array[] = $line;
                }
            }
        }

        return $array;
    }

    /**
     * Summary of rename.
     */
    public static function renameFile($old, $new, $overwrite = true)
    {
        $filesystem = new SFilesystem();
        if (!is_dir(dirname($new))) {
            $filesystem->mkdir(dirname($new));
        }
        NetteFile::rename($old, $new, $overwrite);
    }

    public static function prunedirs($path = null)
    {
        if (null === $path) {
            $path = __CURRENT_DIRECTORY__;
        }

        $command  = [
            '/usr/bin/find',
            $path,
            '-mindepth',
            '1',
            '-type',
            'd',
            '-empty',
            '-delete',
        ];
        $proccess = new ExecProcess($command);
       // utmdd($proccess->getCommandLine());
        $proccess->run();
    }
}
