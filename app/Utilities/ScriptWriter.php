<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Traits\ExecArgs;
use Symfony\Component\Filesystem\Filesystem as SymFs;
use Symfony\Component\Finder\Finder;

use function array_key_exists;
use function count;

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
    public $update = __APP_HOME__ . '/bin/mediaupdate';


    public $ffmpeg = CONFIG['FFMPEG_CMD'];

    /**
     * db.
     *
     * @var string
     */
    public $mediadb = __APP_HOME__ . '/bin/mediadb';

    public $map = __APP_HOME__ . '/bin/mediamap';

    public $clip = __APP_HOME__ . '/bin/mediaclip';

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
        $this->script    = $directory . '/' . $script_name;
        $this->addHeader();
    }

    /**
     * updatePreview.
     */
    public function updatePreview(array $VideoArray)
    {
        // utminfo(func_get_args());

        foreach ($VideoArray as $n => $video) {
            $cmdOptions = ['-U', "-f '" . $video['video_file'] . "'"];
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
        // utminfo(func_get_args());

        $directory         = '"' . __CURRENT_DIRECTORY__ . '"';
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

         $cmdOptions = array_merge( ['--path','"'.__CURRENT_DIRECTORY__.'"'],$cmdOptions);

        $run_cmd = $cmd . ' ' . implode($eol, $cmdOptions);

        if ($comment == true) {
            $this->script_header .= '## CMD=' . $run_cmd . PHP_EOL;
        }

        if ($singleLine === true) {
            $eol = ' \\';
        }

        $this->script_text .= $run_cmd . $eol . PHP_EOL;

        // utmdd($this->script_command);
    }

    /**
     * addFile.
     */
    public function addFile(string $file, bool $string = true)
    {
        // utminfo(func_get_args());

        $file = '"' . $file . '"';
        if ($string === true) {
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

        $this->script_filelist = str_replace("\"\n", '",\\' . PHP_EOL, $this->script_filelist);
        $this->script_text .= str_replace(__CURRENT_DIRECTORY__ . '/', '', $this->script_filelist) . PHP_EOL;
    }

    /**
     * addFileList.
     */
    public function addFileList(array $file_array)
    {
        // utminfo(func_get_args());

        $fileArray = MediaArray::VideoFiles($file_array, 'video_file');
        array_walk($fileArray, function (&$value, $key) {
            $value = '"' . $value . '"';
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

        $deletedOldPatternFile = false;
        $TitleStudio           = trim($TitleStudio, '\\');
        $class                 = trim($class, '\\');
        $extended_class        = 'Patterns';
        $studio                = "public \$studio = '" . $TitleStudio . "';";

        $networkName    = '';
        $networkPath    = '';
        $extended_use   = '';
        $network        = '';
        $OldNetworkFile = '';

        if ($options !== null) {
            if (array_key_exists('ExtendClass', $options)) {
                $extended_class = trim($options['ExtendClass'], '\\');
                // $studio         = "public \$studio = '" . $TitleStudio . "';";

                $extended_use = PHP_EOL . 'use Mediatag\\Patterns\\Studios\\' . $extended_class . ';';
            }
            if (array_key_exists('network', $options)) {
                $network        = "public \$network = '" . $options['network'] . "';";
                $extended_use   = 'use Mediatag\\Patterns\\Studios\\' . $options['networkName'] . '\\' . $extended_class . ';';
                $networkPath    = DIRECTORY_SEPARATOR . $options['networkName'];
                $networkName    = '\\' . $options['networkName'];
                $OldNetworkFile = __PATTERNS_LIB_DIR__ . DIRECTORY_SEPARATOR . __LIBRARY__ . DIRECTORY_SEPARATOR . $options['networkName'] . '.php';
            }
        }

        $OldPatternFile = __PATTERNS_LIB_DIR__ . DIRECTORY_SEPARATOR . __LIBRARY__ . DIRECTORY_SEPARATOR . $class . '.php';
        $Pattern_file   = __PATTERNS_LIB_DIR__ . DIRECTORY_SEPARATOR . __LIBRARY__ . $networkPath . DIRECTORY_SEPARATOR . $class . '.php';

        $Namespace = 'Mediatag\\Patterns\\Studios' . $networkName;

        if (\file_exists($OldNetworkFile)) {
            //$Namespace = 'Mediatag\\Patterns\\Studios' . '\\' . $options['networkName'];
            $php_file = file_get_contents($OldNetworkFile);
            // $NewNamespace   = 'Mediatag\\Patterns\\Studios' . '\\' . $options['networkName'];
            $php_file       = preg_replace('/(namespace )(.*)(;)/', '$1 ' . $Namespace . ' $3', $php_file);
            $NewNetworkFile = __PATTERNS_LIB_DIR__ . DIRECTORY_SEPARATOR . __LIBRARY__ . $networkPath . DIRECTORY_SEPARATOR . $options['networkName'] . '.php';
            \Nette\Utils\FileSystem::createDir(dirname($NewNetworkFile));

            file_put_contents($NewNetworkFile, $php_file);

            \Nette\Utils\FileSystem::delete($OldNetworkFile);
            Mediatag::$Console->writeln('Network file moved to new location OldFile => ' . $OldNetworkFile);
            Mediatag::$Console->writeln('NewFile => ' . $NewNetworkFile);
            exit;

            // utmdd('Network file exists',
            //     [$OldPatternFile,
            //         file_exists($OldPatternFile)],
            //     [$OldNetworkFile,
            //         file_exists($OldNetworkFile)],
            //     $php_file);

            // if (! file_exists($OldPatternFile)) {
            //     return false;
            // }
        }

        if (file_exists($OldPatternFile)) {
            $php_file = file_get_contents($OldPatternFile);
            $php_file = preg_replace('/(namespace )(.*)(;)/', '$1 ' . $Namespace . ' $3', $php_file);
            $php_file = preg_replace('/(class )(.*)( extends )(.*)/', '$1 ' . $class . ' $3 ' . $extended_class, $php_file);
            // if ($network != '') {
            //     $php_file = preg_replace('/(public \$network = )(.*)(;)/', '', $php_file);
            // }

            $php_file = preg_replace('/(public \$studio = )(.*)(;)/', '$1\'' . $TitleStudio . '\'$3', $php_file);

            $php_file = preg_replace('/(use Mediatag\\\\Patterns\\\\Studios\\\\' . $extended_class . ';)/', '', $php_file);

            $php_file = preg_replace('/(class .*)/', PHP_EOL . 'use Mediatag\\Patterns\\Studios\\' . $options['networkName'] . '\\' . $extended_class . ';' . PHP_EOL . '$1', $php_file);
            // utmdd($php_file);

            file_put_contents($Pattern_file, $php_file);
            Mediatag::$Console->writeln('Pattern file moved to new location OldFile => ' . $OldPatternFile);
            Mediatag::$Console->writeln(' NewFile => ' . $Pattern_file);

            \Nette\Utils\FileSystem::delete($OldPatternFile);
            // utmdd('Old Pattern file deleted', $OldPatternFile);
            require_once $Pattern_file;

            return false;
            // } elseif (file_exists($Pattern_file)) {
            //     require_once $Pattern_file;
            //     // utmdd('Pattern already exists');

            //     return false;
            // } else {
            //     if ($deletedOldPatternFile === true) {
            //         require_once $NewNetworkFile;

            //         return false;
            //     }
        }
        // utmdd('Creating Pattern', $Pattern_file);
        $finder     = new Finder;
        $filesystem = new SymFs;

        $finder->files()->in(__DATA_TEMPLATES__)->name('*template.txt');
        foreach ($finder as $file) {
            $name    = $file->getFilenameWithoutExtension();
            ${$name} = $file->getContents();
            // $output->writeln($$name );
            // ...
        }

        $command_array = [
            'EXTEND_USE'   => $extended_use,
            'CLASS_EXTEND' => $extended_class,
            'CLASSNAME'    => $class,
            'STUDIO'       => $studio,
            'NETWORK'      => $network,
            'CLASSNAME_LC' => strtolower($class),
            'CLASSNAME_UC' => strtoupper($class),
            'NAMESPACE'    => $Namespace,
        ];
        // utmdump($command_array);
        foreach ($command_array as $key => $value) {
            $key = '%%' . strtoupper($key) . '%%';
            // if ($value != null) {
            $Patterns_template = str_replace($key, $value, $Patterns_template);
            // }
        }

        Mediatag::$tmpText = '<comment> New Pattern ' . $class . '</comment>';
        // utmdd(['PatternFile' => $Pattern_file, 'Patterns_template' => $Patterns_template]);

        $filesystem->dumpFile($Pattern_file, $Patterns_template);
        // utmdump($Patterns_template);
        require_once $Pattern_file;
    }
    // }
}
