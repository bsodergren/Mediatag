<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\MediaCache;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Callbacks\ProcessCallbacks;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Traits\Callables\Callables;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Utilities\Chooser;
use Nette\Utils\Callback;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

class WriteMeta extends MediatagExec
{

use ProcessCallbacks;
    use MediaFFmpeg;

    public $execMode = 'write';

    public $Display;

    public $cursor;

    public function __construct($videoData, $input = null, $output = null)
    {
        // utminfo(func_get_args());

        $this->execMode = 'write';
        parent::__construct($videoData, $input, $output);
    }

    public function clearMeta($options = null)
    {
        // utminfo(func_get_args());
        if (null === Option::getValue('empty', 1)) {
            $this->addOptionArg('--metaEnema');
        } else {
            foreach (__META_TAGS__ as $tag) {
                $this->createOptionArg($tag, '');
            }
        }

        $this->addOptionArg('--overWrite');
        //  MediaCache::forget($this->video_key);
        $this->write();
    }

    public function writeChanges($options = null)
    {
        // utminfo(func_get_args());

        $update = false;
        if (\count($this->updateTags) > 0) {
            foreach (__META_TAGS__ as $tag) {
                if (\array_key_exists($tag, $this->updateTags)) {
                    $update = true;
                    $this->createOptionArg($tag, $this->updateTags[$tag]);
                }
            }
            if (true === $update) {
                // $videoData[$this->video_key] = $this->videoData;
                // $videoData[$this->video_key]['metatags'] = array_merge($this->videoData['currentTags'],$this->videoData['updateTags']);

                // unset($videoData[$this->video_key]['currentTags']);
                // unset($videoData[$this->video_key]['updateTags']);
                // unset($videoData[$this->video_key]['video_key']);

                // utmdd($videoData);

                $this->addOptionArg('--overWrite');
                //   MediaCache::put($this->video_key,$videoData);
                $this->write();
            }
        }

        return null;
    }

    public function write()
    {
        // utminfo(func_get_args());

        $this->errors = null;

        $command = [
            Mediatag::App(),
            $this->video_file,
        ];

        $command = array_merge($command, $this->getOptionArgs());
        // utmdd([__METHOD__,$command]);
        // // UTMlog::Logger('Writing Metadata', $run_cmd);

        if (1 == Option::isTrue('changes')) {
            if (null === Chooser::$bypass) {
                $go = Chooser::changes();
            }
        } else {
            $go = true;
        }

        if (true === Chooser::$bypass || true === $go) {
            $callback = Callback::check([$this, 'WriteMetaOutput']);

            $this->exec($command, $callback);
            MediaCache::forget($this->video_key);

            $results = ('' != $this->errors) ? $this->errors : $this->stdout;
        } else {
            $results = false;
            $this->output->write("\t Skipping ".basename($command[1]));
        }
        if (true == $results) {
                        utmdump($results);

            if (str_contains($results, 'signal') ||
            str_contains($results, 'error')) {
                // // UTMlog::logError('results Metadata', $results);
                if (str_contains($results, '11')
                || str_contains($results, 'alignment')) {
                    // $io->error([$this->video_file, $results]);
                    // UTMlog::logError('process with FFMPEG');

                    $this->Display->processOutput->overwrite('<info>Reparing Video</info>');

                    $this->repairVideo();
                } else {
                    $this->output->write("\t ".$results.\PHP_EOL);
                    $this->output->write("\t -- Running ".$this->runCommand);

                    exit;
                }
            }
        }
    }
}
