<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Filesystem;

use const PHP_EOL;

use Mediatag\Utilities\MediaArray;
use Nette\Utils\Callback;
use Nette\Utils\FileSystem as NetteFile;
use Nette\Utils\Strings;
use SplFileObject;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;
use Symfony\Component\Finder\Finder as SFinder;
use Symfony\Component\Process\Process as ExecProcess;

use function array_key_exists;
use function call_user_func_array;
use function count;
use function dirname;
use function is_array;
use function is_string;

/**
 * Filesystem.
 */
class MediaFilesystem extends SFilesystem
{
    public static $clean_up = 3;

    private static $tempdir = '.bak';

    public static function __callStatic($method, $params)
    {
        // utminfo(func_get_args());

        $methodArray = get_class_methods(NetteFile::class);
        if (MediaArray::search($methodArray, $method) !== null) {
            $handler = ['Nette\Utils\FileSystem', $method];

            return call_user_func_array($handler, $params);
        }
    }

    /**
     * getRelative.
     *
     * @param  mixed  $path
     */
    public static function getRelative(string $path): string
    {
        // utminfo(func_get_args());

        $new_path = (new SFilesystem)->makePathRelative($path, __CURRENT_DIRECTORY__);

        return rtrim($new_path, '/');
    }

    /**
     * writeFile.
     */
    public static function writeFile($file, $content, $backup = true)
    {
        // utminfo(func_get_args());S

        if (! file_exists($file)) {
            touch($file);
        }

        if (is_array($content)) {
            $content_string = implode("\n", $content);
        } else {
            $content_string = $content;
        }
        if ($backup === true) {
            self::backupPlaylist($file);
        }
        $content_string .= PHP_EOL; //. '#  file'.PHP_EOL;

        $out = file_put_contents($file, $content_string . PHP_EOL);
    }

    public static function writePlaylist($file, $content)
    {
        // utminfo(func_get_args());

        $lineArray = [];
        foreach ($content as $line) {
            if (str_contains($line, '&')) {
                $lineArray[] = Strings::before($line, '&');
            } else {
                $lineArray[] = $line;
            }
        }
        if (count($lineArray) > 0) {
            self::writeFile($file, $lineArray);
        }
    }

    /**
     * writeArray.
     *
     * @param  mixed  $content
     */
    public static function writeArray($file, $arrayName, array $content)
    {
        // utminfo(func_get_args());

        foreach ($content as $i => $line) {
            $studio = $line;
            $key    = $line;
            if (str_contains($line, ':')) {
                $key    = Strings::before($line, ':');
                $studio = Strings::after($line, ':');
            }
            $fileArray[$key] = $studio;
        }

        //        utmdd([__METHOD__,$fileArray]);

        self::backupPlaylist($file);
        file_put_contents($file, '<?php $' . $arrayName . ' = ' . var_export($fileArray, true) . ';');
    }

    public static function backupPlaylist($filename, $directory = false, $move = false)
    {
        // utminfo(func_get_args());

        if (! file_exists($filename)) {
            return 0;
        }

        $filesystem    = new SFilesystem;
        $file          = realpath($filename);
        $filename      = basename($file);
        $fileNameNoExt = Strings::before($filename, '.', 1);

        if ($directory === false) {
            $directory = dirname($file);
            $directory = $directory . '/' . self::$tempdir . '/' . $fileNameNoExt;
        }

        if (! is_dir($directory)) {
            $filesystem->mkdir($directory);
        }

        $backupFile = $directory . '/' . $filename;

        $ext = '';

        if (Strings::after($backupFile, '.', -1) == 'old') {
            $fileNo = 1;
        }
        if (is_numeric(Strings::after($backupFile, '.', -1))) {
            $number = Strings::after($backupFile, '.', -1);
            $fileNo = $number + 1;
        }

        if (isset($fileNo)) {
            if (self::$clean_up > 0) {
                if (self::$clean_up < $fileNo) {
                    MediaFinder::$quiet = true;
                    $r                  = MediaFinder::find($filename . '*', $directory);
                    MediaFinder::$quiet = false;
                    rsort($r);
                    unlink($r[0]);
                    foreach ($r as $idx => $rfile) {
                        if (array_key_exists($idx + 1, $r)) {
                            (new SFilesystem)->rename($r[$idx + 1], $r[$idx]);
                        }
                    }
                    $fileNo = '';
                } else {
                    $ext = '.' . $fileNo;
                }
            } else {
                $ext = '.' . $fileNo;
            }
        }

        $backup_name1 = $fileNameNoExt . '.old' . $ext;

        $backup_name = $directory . '/' . basename($backup_name1);

        if (file_exists($backup_name)) {
            self::backupPlaylist($backup_name, $directory, $move);
        }

        if ($move == true) {
            (new SFilesystem)->rename($file, str_replace('.old', '.txt', $backup_name));
        } else {
            (new SFilesystem)->copy($file, $backup_name);
        }

        // return $backup_name;
    }

    /**
     * RenameDir.
     */
    public static function RenameDir($old, $new)
    {
        // utminfo(func_get_args());

        $file_array = [];
        $finder     = new SFinder;
        $filesystem = new SFilesystem;

        if (is_dir($new)) {
            $finder->files()->in($old)->sortByName();
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $old_file = $file->getRealPath();
                    $newpath  = $new . str_replace($old, '', $file->getPath());
                    if (! is_dir($newpath)) {
                        $filesystem->mkdir($newpath);
                    }
                    $new_file = $newpath . '/' . $file->getBasename();

                    $oldFile = self::getRelative($old_file);
                    $newFile = self::getRelative($new_file);

                    echo "Renaming {$oldFile} to {$newFile}" . PHP_EOL;
                    NetteFile::rename($old_file, $new_file);
                }
            } else {
                NetteFile::delete($old);
                echo "No files in {$old} " . PHP_EOL;
            }
        } else {
            $oldFile = self::getRelative($old);
            $newFile = self::getRelative($new);
            echo "Renaming {$oldFile} to {$newFile}" . PHP_EOL;

            NetteFile::rename($old, $new);
        }
    }

    public static function readLineNo($filename, $getLine)
    {
        $file = new SplFileObject($filename);
        // foreach ($file as $k => $line) {
        //     utmdump(($file->key() + 1) . ': ' . $file->current());
        // }
        $file->seek($getLine - 1);

        return $file->current();
    }

    public static function readLines($file, $callback = false)
    {
        // utminfo(func_get_args());

        if (! file_exists($file)) {
            return false;
        }

        if (is_string($callback)) {
            $n        = new self;
            $callback = Callback::check([$n, $callback]);
        }

        if (is_array($callback)) {
            $callback = Callback::check($callback);
        }
        // if(is_callable($callback)){

        // }

        $text  = NetteFile::readLines($file);
        $array = [];

        foreach ($text as $lineNum => $line) {
            if ($line != '') {
                if ($callback == true) {
                    $res = $callback($line);
                    if ($res !== false) {
                        if (is_array($res)) {
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
        // utminfo(func_get_args());

        $filesystem = new SFilesystem;
        if (! is_dir(dirname($new))) {
            $filesystem->mkdir(dirname($new));
        }
        NetteFile::rename($old, $new, $overwrite);
    }

    public static function prunedirs($path = null)
    {
        // utminfo(func_get_args());

        if ($path === null) {
            $path = __CURRENT_DIRECTORY__;
        }

        $command = [
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
