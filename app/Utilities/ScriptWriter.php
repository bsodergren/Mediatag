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
    public $update       = __APP_HOME__ . '/bin/mediaupdate';

    /**
     * db.
     *
     * @var string
     */
    public $db           = __APP_HOME__ . '/bin/mediadb';
    public $map          = __APP_HOME__ . '/bin/mediamap';

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

    public $optionArgs   = [];

    /**
     * __construct.
     *
     * @param string $script_name
     * @param string $directory
     */
    public function __construct(string $script_name, string $directory)
    {
        utminfo();

        $this->directory = $directory;
        $this->script    = $directory . '/' . $script_name;
        $this->addHeader();
    }

    /**
     * updatePreview.
     *
     * @param array $VideoArray
     */
    public function updatePreview(array $VideoArray)
    {
        utminfo();


        foreach ($VideoArray as $n => $video) {
            $cmdOptions = ["-U","-f '" . $video['video_file'] . "'"];
            foreach ($video['updateTags'] as $meta_tag => $meta_value) {
                $cmdOptions[] = "\t --" . $meta_tag . "='" . $meta_value . "'";
            }
            $this->addCmd('update', $cmdOptions, false, false);
        }

    }

    /**
     * addHeader.
     */
    public function addHeader()
    {
        utminfo();

        $directory           = '"' . __CURRENT_DIRECTORY__ . '"';
        $this->script_header = <<<EOD
#!/bin/bash
DIR={$directory}


EOD;
    }

    /**
     * addCmd.
     *
     * @param string $command
     * @param array $cmdOptions
     * @param bool $comment
     * @param bool $singleLine
     */
    public function addCmd(string $command, array $cmdOptions = [], bool $comment = true, bool $singleLine = false)
    {
        utminfo();

        $eol     = " ";


        $cmd     = $this->{$command};

        $run_cmd = $cmd . ' ' . implode($eol, $cmdOptions);

        if (true == $comment) {
            $this->script_header .= '## CMD=' . $run_cmd . \PHP_EOL;
        }

        if ($singleLine === true) {
            $eol = " \\";

        }

        $this->script_command .= $run_cmd . $eol . \PHP_EOL;
    }

    /**
     * addFile.
     *
     * @param string $file
     * @param bool $string
     */
    public function addFile(string $file, bool $string = true)
    {
        utminfo();

        $file = '"' . $file . '"';
        if (true === $string) {
            $this->script_filelist .= $file;
        } else {
            $this->fileListAray[] = $file;
        }
    }

    /**
     * addFileList.
     *
     * @param array $file_array
     */
    public function addFileList(array $file_array)
    {
        utminfo();

        $fileArray = MediaArray::VideoFiles($file_array, 'video_file');
        array_walk($fileArray, function (&$value, $key) {
            $value = '"' . $value . '"';
        });

        $file_list = implode("\n", $fileArray);
        $this->script_filelist .= $file_list;
    }

    /**
     * write.
     *
     * @param bool $singleLine
     */
    public function write(bool $singleLine = true)
    {
        utminfo();

        if (file_exists($this->script)) {
            Filesystem::delete($this->script);
        }
        if (\count($this->fileListAray) > 0) {
            $file_list = implode("\n", $this->fileListAray);
            $this->script_filelist .= $file_list;
        }

        if (true == $singleLine) {
            $this->script_filelist = str_replace("\"\n", '",\\' . \PHP_EOL, $this->script_filelist);
        }


        $this->script_filelist = str_replace(__CURRENT_DIRECTORY__ . '/', '', $this->script_filelist);


        $this->script_text     = $this->script_header . PHP_EOL . $this->script_command . $this->script_filelist;

        FileSystem::write($this->script, $this->script_text, 0755);
    }

    /**
     * addPattern.
     *
     * @param string $class
     * @param string $TitleStudio
     * @param array $options
     */
    public static function addPattern(string $class, string $TitleStudio, array $options = [])
    {
        utminfo();

        $TitleStudio = trim($TitleStudio,'\\');
        $class = trim($class,'\\');
        $extended_class = 'Patterns';
        $studio         = "public \$studio = '" . $TitleStudio . "';";
        // $extended_use   = 'use Mediatag\\Modules\\TagBuilder\\Patterns;';

        if ($options !== null) {
            if(array_key_exists('ExtendClass',$options)){
            $extended_class =trim( $options['ExtendClass'],'\\');;
            $studio         = "public \$studio = '" . $TitleStudio . "';";

                  $extended_use   = 'use Mediatag\\Patterns\\Studios\\' . $extended_class . ';';

        }

    }

        $Pattern_file   = __PATTERNS_LIB_DIR__ . \DIRECTORY_SEPARATOR . __LIBRARY__ . \DIRECTORY_SEPARATOR . $class . '.php';

        if (! file_exists($Pattern_file)) {

            $finder            = new Finder();
            $filesystem        = new SymFs();

            $finder->files()->in(__DATA_TEMPLATES__)->name('*template.txt');
            foreach ($finder as $file) {
                $name    = $file->getFilenameWithoutExtension();
                ${$name} = $file->getContents();
                // $output->writeln($$name );
                //...
            }
            $command_array     = [
                'EXTEND_USE'   => $extended_use,
                'CLASS_EXTEND' => $extended_class,
                'CLASSNAME'    => $class,
                'STUDIO'       => $studio,
                'CLASSNAME_LC' => strtolower($class),
                'CLASSNAME_UC' => strtoupper($class),
                'LIBRARY'      => __LIBRARY__,
            ];
            foreach ($command_array as $key => $value) {
                $key = '%%' . strtoupper($key) . '%%';
                if (null != $value) {
                    $Patterns_template = str_replace($key, $value, $Patterns_template);
                }
            }

            Mediatag::$tmpText = '<comment> New Pattern ' . $class . '</comment>';
            $filesystem->dumpFile($Pattern_file, $Patterns_template);
        }
    }
}
