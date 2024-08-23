<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\Mediatag;


use UTM\Bundle\Monolog\UTMLog;
use Mediatag\Core\MediaCache;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Traits\Callables;
use Mediatag\Traits\ffmpeg;
use Mediatag\Utilities\Chooser;
use UTM\Utilities\Debug\Timer;
use UTM\Utilities\Option;
use Nette\Utils\Callback;

class WriteExec extends MediatagExec
{
    use Callables;
    use ffmpeg;

    public $execMode = 'write';

    public $Display;

    public $cursor;

    public function __construct($videoData, $input = null, $output = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->execMode = 'write';
        parent::__construct($videoData, $input, $output);
    }

    public function clearMeta($options = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        if (null === Option::getValue('empty', 1)) {
            $this->addOptionArg('--metaEnema');
        } else {
            foreach (__META_TAGS__ as $tag) {
                $this->createOptionArg($tag, '');
            }
        }

        $this->addOptionArg('--overWrite');
        $this->write();
    }

    public function writeChanges($options = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $update = false;
        if (\count($this->updateTags) > 0) {
            foreach (__META_TAGS__ as $tag) {
                if (\array_key_exists($tag, $this->updateTags)) {
                    $update = true;
                    $this->createOptionArg($tag, $this->updateTags[$tag]);
                }
            }
            if (true === $update) {
                $this->addOptionArg('--overWrite');
                $this->write();
            }
        }

        return null;
    }

    public function write()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->errors = null;
        $video_key    = File::file($this->video_file, 'videokey');

        $command      = [
            Mediatag::App(),
            $this->video_file,
        ];

        $command      = array_merge($command, $this->getOptionArgs());
        // utmdd([__METHOD__,$command]);
        // UTMLog::Logger('Writing Metadata', $run_cmd);

        if (1 == Option::isTrue('changes')) {
            if (null === Chooser::$bypass) {
                $go = Chooser::changes($this->input, $this->output);
            }
        } else {
            $go = true;
        }

        if (true === Chooser::$bypass || true === $go) {
            $callback = Callback::check([$this, 'WriteMetaOutput']);
            Timer::watch('Write File data', $this->video_name);
            $this->exec($command, $callback);
            Timer::watch('End Writing data');
            MediaCache::forget($video_key);
            $results  = ('' != $this->errors) ? $this->errors : $this->stdout;
        } else {
            $results = false;
            $this->output->write("\t Skipping " . basename($command[1]));
        }
        if (true == $results) {
            if (str_contains($results, 'error')) {
                // UTMLog::logError('results Metadata', $results);
                if (str_contains($results, 'insufficient')
                || str_contains($results, 'alignment')) {
                    // $io->error([$this->video_file, $results]);
                    UTMLog::logError('process with FFMPEG');

                    $this->Display->processOutput->overwrite('<info>Reparing Video</info>');
                    Timer::watch('Reparing Video data');

                    $this->repairVideo();
                    Timer::watch('End REparing Video');
                } else {
                    $this->output->write("\t " . $results . \PHP_EOL);
                    $this->output->write("\t -- Running " . $this->runCommand);

                    exit;
                }
            }
        }
    }
}
