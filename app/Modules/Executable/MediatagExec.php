<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Metatags\Artist;
use Mediatag\Traits\Callables;
use Mediatag\Traits\CmdProcess;
use Mediatag\Traits\ExecArgs;
use Mediatag\Traits\preview;

use Mediatag\Traits\Test;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;

class MediatagExec
{
    use Callables;
    use CmdProcess;
    use ExecArgs;

    use preview {
        preview::preview as previewTrait;
    }
    use Test {
        Test::test as testTrait;
    }

    public $metatags      = [];

    public $stdout;

    public $errors;

    public $video_file;

    public $video_name;

    public $video_key;

    public $video_path;

    public $video_library;

    public $input;

    public $output;

    public $runCommand;

    public $updateTags    = [];

    public $execMode      = '';

    protected $optionArgs = [];

    public function __construct($videoData, $input = null, $output = null)
    {
        utminfo();

        //        $this->getTags();

        if (\is_string($videoData)) {
            if (file_exists($videoData)) {
                $videoData = (new File($videoData))->get();
            }
        }

        foreach ($videoData as $key => $value) {
            if (\is_array($value)) {
                foreach ($value as $key_a => $val_a) {
                    $this->{$key}[$key_a] = $val_a;
                }
            } else {
                $this->{$key} = $value;
            }
        }

        if (null !== $input) {
            $this->input = $input;
        }
        if (null !== $output) {
            $this->output = $output;
        }
    }

    public function preview()
    {
        utminfo();

        if ('write' == $this->execMode) {
            //   $this->previewTrait("\t Running ".$this->runCommand, true);
        }
    }

    public function test()
    {
        utminfo();

        if ('write' == $this->execMode) {
            $this->testTrait("\t Running " . $this->runCommand, true);
        }
    }

    public function getTags()
    {
        utminfo();

        foreach (__META_TAGS__ as $value) {
            $this->metatags[$value] = '';
        }
    }

    protected function createOptionArg($meta_tag, $meta_value)
    {
        utminfo();


        $this->getCmdArgs($meta_tag, $meta_value);

        // if ('artist' == $meta_tag) {
        //     $this->addOptionArg('--rDNSatom');
        //     if ('' != $meta_value) {
        //         $xml_value = Artist::ArtistXML($meta_value);
        //         $this->addOptionArg($xml_value);
        //         $this->addOptionArg('name=iTunMOVI');
        //         $this->addOptionArg('domain=com.apple.iTunes');
        //     } else {
        //         $this->addOptionArg('');
        //         $this->addOptionArg('name=');
        //         $this->addOptionArg('domain=');
        //     }

        //     $this->addOptionArg('--albumArtist='.$meta_value);
        // } elseif ('studio' == $meta_tag) {
        //     $this->addOptionArg('--album='.$meta_value);
        // } else {
        //     $this->addOptionArg('--'.$meta_tag.'='.$meta_value);
        // }
    }
    // protected function testexec($command, $callback = null): mixed
    // {
    //     $process = new Process($command);
    //     $process->setTimeout(60000);

    //     $this->runCommand = $process->getCommandLine();
    //     utmdd($this->runCommand);


    //     return true;
    // }
    protected function exec($command, $callback = null): mixed
    {
        utminfo();

        $process          = new Process($command);
        $process->setTimeout(60000);

        $this->runCommand = $process->getCommandLine();
        // UTMlog::Logger('Executing', $this->runCommand);
        $this->preview();
        $this->test();

        $process->start();
        $process->wait($callback);
        return $this->errors;
    }
}
