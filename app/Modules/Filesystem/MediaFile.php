<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Filesystem;

use Mediatag\Core\Mediatag;
use Mediatag\Traits\Callables;
use UTM\Utilities\Debug\Debug;
use Mediatag\Utilities\Strings;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem as NetteFile;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;

class MediaFile
{
    use Callables;

    public $video_file;

    public $video_key;

    public $video_name;

    public $video           = [];

    public $output;

    public $finder;

    public $video_library;

    public static $clean_up = 3;

    private $file_path;

    private $filename;

    private $full_path;

    private $studio_name;

    private $full_filename;

    private static $tempdir = '.bak';

    public function __construct($filename = null)
    {
        utminfo(func_get_args());

        if (null !== $filename) {
            $this->video_file = $filename;
            $this->video_name = $this->filename();

            if ('' == $this->video_name) {
                $this->video_name = $filename;
            }
        }
    }

    /**
     * get.
     */
    public function get(): array
    {
        utminfo(func_get_args());

        $this->video = [
            'video_file'    => $this->fullname(),
            'video_path'    => $this->filepath(),
            'video_name'    => $this->filename(),
            'video_key'     => $this->videokey(),
            'video_library' => $this->library(),
        ];

        return $this->video;
    }

    private static function get64BitNumber($text)
    {
        utminfo(func_get_args());

        $alpha   = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $numeric = [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 6, 1, 2, 3, 4, 5];
        // $text = basename($text);
        //  $text    = strtolower($text);
        $key     =  md5($text);
        // $key = str_replace($alpha, $numeric, $text);
        // $key = str_replace(['_', '-', '.', '/', ' '], '', $key);
        // $len = \strlen($key);
        $xkey    = 'x' . substr($key, 0, 31);
        return $xkey;

    }

    public static function getVideoKey($filename)
    {
        utminfo(func_get_args());


        // $filename = realpath($filename);

        $filename = basename($filename);

        $success  = preg_match('/-(p?h?[a-z0-9]{6,}).mp4/i', $filename, $matches);
        if (1 == $success) {
            $video_key = $matches[1];
        } else {
            $video_key = self::get64BitNumber($filename);
            //$video_key = 'x'.$video_key;
        }

        return $video_key;
    }

    /**
     * videokey.
     */
    public function videokey()
    {
        utminfo(func_get_args());

        if ('' != $this->video_file) {
            if (false == $this->video_key) {
                $this->video_key = self::getVideoKey($this->video_file);
            }
        }
        return $this->video_key;

        // return [$this->video_key,$success,$matches];
    }

    /**
     * fullname.
     */
    public function fullname(): string
    {
        utminfo(func_get_args());

        return $this->path('fullname');
    }

    /**
     * filepath.
     */
    public function filepath(): string
    {
        utminfo(func_get_args());

        return $this->path('fullpath');
    }

    /**
     * filename.
     */
    public function filename(): string
    {
        utminfo(func_get_args());

        return $this->path('filename');
    }

    /**
     * library.
     */
    public function library(): string
    {
        utminfo(func_get_args());

        if ($this->video_file) {
            $directory = $this->filepath();
        }

        $filesystem   = new SFilesystem();
        $in_directory = $filesystem->makePathRelative($directory, __PLEX_HOME__);

        preg_match('/([^\/]*)\/([^\/]+)?/', $in_directory, $match);
        if (Arrays::contains(__LIBRARIES__, $match[1])) {
            $this->video_library = $match[1];
        }

        return $this->video_library;
    }

    /**
     * studioName.
     */
    public function studioName(): string
    {
        utminfo(func_get_args());

        return $this->path('studioName');
    }

    /**
     * file.
     *
     * @param mixed $filename
     * @param mixed $method
     */
    public static function file(string $filename, string $method = 'get'): mixed
    {
        utminfo(func_get_args());

        $obj = new static($filename);

        return $obj->{$method}();
    }

    public static function splitFile($source, $targetpath = './logs/', $lines = 10, $filename = 'videos_', $ext = '')
    {
        utminfo(func_get_args());

        $i      = 0;
        $j      = 1;
        $buffer = '';

        if (!str_ends_with($targetpath, '/')) {
            $targetpath .= '/';
        }

        NetteFile::createDir($targetpath);

        $handle = @fopen($source, 'r');

        while (!feof($handle)) {
            $buffer .= @fgets($handle, 4096);
            ++$i;

            $file_name = sprintf('%s%02d%s', $filename, $j, $ext);
            $fname     = $targetpath . $file_name;

            Strings::showstatus($i, $lines, 80, $file_name);
            if ($i >= $lines) {
                self::saveToFile($buffer, $fname);
                ++$j;
                $i = 0;
            }
        }
        Strings::showstatus($i, $i, 80, $file_name);

        self::saveToFile($buffer, $fname);
        fclose($handle);

        return 1;
    }

    public static function saveToFile(&$buffer, $fname)
    {
        utminfo(func_get_args());

        if (!$fhandle = @fopen($fname, 'w')) {
            echo "Cannot open file ({$fname})";

            exit;
        }
        if (!@fwrite($fhandle, $buffer)) {
            echo "Cannot write to file ({$fname})";

            exit;
        }
        fclose($fhandle);
        $buffer = '';
    }

    public static function file_append_file($file = '', $string = '')
    {
        utminfo(func_get_args());
        $dir     = realpath($file);
        if ($dir === false) {
            $dirname = dirname($file);

            NetteFile::createDir($dirname);
        }

        $fp      = fopen($file, 'a+');
        fwrite($fp, $string);
        fclose($fp);
    }

    /**
     * path.
     */
    private function path($method = 'filename'): string
    {
        utminfo(func_get_args());

        $this->full_filename = realpath($this->video_file);
        $path_parts          = pathinfo($this->full_filename);

        return match ($method) {
            'fullname' => $this->full_filename,
            'filename' => $path_parts['basename'],
            'fullpath' => $path_parts['dirname'],
        };
    }

    public static function isPornhubfile($filename)
    {
        utminfo(func_get_args());

        $filesystem   = new SFilesystem();
        $in_directory = $filesystem->makePathRelative(__CURRENT_DIRECTORY__, __PLEX_HOME__);

        preg_match('/([^\/]*)\/([^\/]+)?/', $in_directory, $match);

        if (Arrays::contains(__LIBRARIES__, $match[1])) {
            $library = $match[1];
        }

        $success      = preg_match('/-(p?h?[a-z0-9]{6,}).mp4/i', $filename, $matches);
        if (1 == $success) {
            return true;
        }
        if (str_contains($filename, '000K')) {
            return true;
        }
        if (str_contains($filename, '1500K')) {
            return true;
        }
        if (str_contains($filename, 'Pornhub')) {
            return true;
        }
        if ('Studios' == $library) {
            return false;
        }

        return false;
    }
}
