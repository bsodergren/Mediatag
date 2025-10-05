<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Traits\ExecArgs;
use Symfony\Component\Filesystem\Filesystem as SymFs;
use Symfony\Component\Finder\Finder;

use function array_key_exists;
use function count;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

/**
 * ScriptWriter.
 */
class ScriptWriter
{
    use ExecArgs;
    /**
     * script_text.
     */
    public $script_header;
    public $script_command;
    public $script_filelist;
    public $script_text;

    /**
     * update.
     *
     * @var string
     */
    public $update = __APP_HOME__.'/bin/mediaupdate';

    public $ffmpeg = CONFIG['FFMPEG_CMD'];

    /**
     * db.
     *
     * @var string
     */
    public $db   = __APP_HOME__.'/bin/mediadb';
    public $map  = __APP_HOME__.'/bin/mediamap';
    public $clip = __APP_HOME__.'/bin/mediaclip';
    /**
     * script.
     */
    public $script;

    public $directory;

    /**
     * fileListAray.
     *
     * @var array
     */
    public $fileListAray = [];

    public $optionArgs = [];

    /**
     * __construct.
     */
    public function __construct(string $script_name, string $directory)
    {
        // utminfo(func_get_args());

        $this->directory = $directory;
        $this->script    = $directory.'/'.$script_name;
        $this->addHeader();
    }

    /**
     * updatePreview.
     */
    public function updatePreview(array $VideoArray)
    {
        // utminfo(func_get_args());

        foreach ($VideoArray as $n => $video) {
            $cmdOptions = ['-U', "-f '".$video['video_file']."'"];
            foreach ($video['updateTags'] as $meta_tag => $meta_value) {
                $cmdOptions[] = "\t --".$meta_tag."='".$meta_value."'";
            }
            $this->addCmd('update', $cmdOptions, false, false);
        }
    }

    /**
     * addHeader.
     */
    public function addHeader()
    {
        // utminfo(func_get_args());

        $directory         = '"'.__CURRENT_DIRECTORY__.'"';
        $this->script_text = <<<EOD
#!/bin/bash
DIR={$directory}


EOD;
    }

    /**
     * addCmd.
     */
    public function addCmd(string $command, array $cmdOptions = [], bool $comment = true, bool $singleLine = false)
    {
        // utminfo(func_get_args());

        $eol = ' ';

        $cmd = $this->{$command};

        $run_cmd = $cmd.' '.implode($eol, $cmdOptions);

        if (true == $comment) {
            $this->script_header .= '## CMD='.$run_cmd.PHP_EOL;
        }

        if (true === $singleLine) {
            $eol = ' \\';
        }

        $this->script_text .= $run_cmd.$eol.PHP_EOL;

        // utmdd($this->script_command);
    }

    /**
     * addFile.
     */
    public function addFile(string $file, bool $string = true)
    {
        // utminfo(func_get_args());

        $file = '"'.$file.'"';
        if (true === $string) {
            $this->script_filelist .= $file;
        } else {
            $this->fileListAray[] = $file;
        }
    }

    public function addFiles()
    {
        if (count($this->fileListAray) > 0) {
            $file_list = implode("\n", $this->fileListAray);
            $this->script_filelist .= $file_list;
        }

        $this->script_filelist = str_replace("\"\n", '",\\'.PHP_EOL, $this->script_filelist);

        $this->script_text .= str_replace(__CURRENT_DIRECTORY__.'/', '', $this->script_filelist).PHP_EOL;
    }

    /**
     * addFileList.
     */
    public function addFileList(array $file_array)
    {
        // utminfo(func_get_args());

        $fileArray = MediaArray::VideoFiles($file_array, 'video_file');
        array_walk($fileArray, function (&$value, $key) {
            $value = '"'.$value.'"';
        });

        $this->fileListAray = $fileArray;
    }

    /**
     * write.
     */
    public function write(bool $singleLine = true)
    {
        // utminfo(func_get_args());

        if (file_exists($this->script)) {
            Filesystem::delete($this->script);
        }

        // $this->script_text = $this->script_header.\PHP_EOL.$this->script_command.$this->script_filelist;

        Filesystem::write($this->script, $this->script_text, 0755);
    }

    /**
     * addPattern.
     */
    public static function addPattern(string $class, string $TitleStudio, array $options = [])
    {
        // utminfo(func_get_args());

        $TitleStudio    = trim($TitleStudio, '\\');
        $class          = trim($class, '\\');
        $extended_class = 'Patterns';
        $studio         = "public \$studio = '".$TitleStudio."';";
        $extended_use   = ' ';
        $network        = ' ';
        if (null !== $options) {
            if (array_key_exists('ExtendClass', $options)) {
                $extended_class = trim($options['ExtendClass'], '\\');
                // $studio         = "public \$studio = '" . $TitleStudio . "';";

                $extended_use = PHP_EOL.'use Mediatag\\Patterns\\Studios\\'.$extended_class.';';
            }
            if (array_key_exists('network', $options)) {
                $network = "public \$network = '".$options['network']."';";
            }
        }

        $Pattern_file = __PATTERNS_LIB_DIR__.DIRECTORY_SEPARATOR.__LIBRARY__.DIRECTORY_SEPARATOR.$class.'.php';

        // if (! file_exists($Pattern_file)) {

        $finder     = new Finder();
        $filesystem = new SymFs();

        $finder->files()->in(__DATA_TEMPLATES__)->name('*template.txt');
        foreach ($finder as $file) {
            $name    = $file->getFilenameWithoutExtension();
            ${$name} = $file->getContents();
            // $output->writeln($$name );
            // ...
        }
        $command_array = [
            'EXTEND_USE'    => $extended_use,
            'CLASS_EXTEND'  => $extended_class,
            'CLASSNAME'     => $class,
            'STUDIO'        => $studio,
            'NETWORK'       => $network,
            'CLASSNAME_LC'  => strtolower($class),
            'CLASSNAME_UC'  => strtoupper($class),
            'LIBRARY'       => __LIBRARY__,
        ];
        foreach ($command_array as $key => $value) {
            $key = '%%'.strtoupper($key).'%%';
            if (null != $value) {
                $Patterns_template = str_replace($key, $value, $Patterns_template);
            }
        }

        Mediatag::$tmpText = '<comment> New Pattern '.$class.'</comment>';
        $filesystem->dumpFile($Pattern_file, $Patterns_template);
        // utmdump($Pattern_file);
        require_once $Pattern_file;
    }
    // }
}
