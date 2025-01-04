<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\ShowDisplay;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\VideoData\Data\VideoPreview;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

include_once __DATA_MAPS__.'/WordMap.php';

class Process extends Mediatag
{
    use Helper;

    public $VideoList = [];

    public $defaultCommands = [
        'exec' => null,
    ];

    public $commandList = [
        'colors'     => [
            'colors'      => null,
        ],
        
    ];

    public $csvfilename = __DOWNLOAD_DIR__.'/pornhub.com-db.csv';

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
        // utminfo(func_get_args());
        if (Option::isTrue('colors')) {
           \define('SKIP_SEARCH', true);
        }
        parent::boot($input, $output);

        //  utmdd(Command::$logger);

        // parent::$Display              = new ShowDisplay($output);
    }

    public function __call($m, $a)
    {
        // utminfo(func_get_args());

        return null;
    }

    public function exec($option = null)
    {
        // utminfo(func_get_args());
        $path = getcwd();

        // $finder          = new MediaFinder();
        // $this->VideoList = $finder->Search($path, '/\.mov$/i');

        // parent::$SearchArray = $this->VideoList;
        $this->VideoList     = parent::getVideoArray();
        // //
        // foreach ($this->VideoList['file'] as $key => $videoInfo) {
        //     // $preview    = new VideoPreview();
        //     // $previewLoc = $preview->BuildPreview($videoInfo);

        //     $file[] = $videoInfo['video_file'];

        // }
        // $outputFile = Option::getValue('output', true);

        // // $ffprobe = FFProbe::create();
        // // $ret = $ffprobe
        // //     ->streams($file[1]) // extracts streams informations
        // //     ->videos()                      // filters video streams
        // //     ->first()                       // returns the first video stream
        // //     ->all();
        // // utmdd($ret);
        // $ffmpeg = FFMpeg::create(['timeout' => 3000], Command::$logger);
        // $video  = $ffmpeg->open($file[1]);

        // $tmpFile = dirname($file[1]) . DIRECTORY_SEPARATOR . "tmp_" . basename($file[1]);

        // $video->filters()->clip(TimeCode::fromSeconds(3.5));
        // $format = new \FFMpeg\Format\Video\X264('copy', 'copy');
        // $format->setPasses(1);
        // $video->save($format, $tmpFile);
        // //  $r = $video->getFFMpegDriver()->getProcessRunner();
        // // utmdump($r);
        // unset($ffmpeg);
        // unset($video);

        // $ffmpeg = FFMpeg::create(['timeout' => 3000], Command::$logger);
        // $video  = $ffmpeg->open($file[0]);

        // if ($outputFile === null) {
        //     $key        = $videoInfo['video_key'];
        //     $filename   = basename($file[1], '.mp4');
        //     $firstVideo = basename($file[0], '.mp4');
        //     $secondName = str_split($filename);
        //     $firstName  = str_split($firstVideo);
        //     foreach ($secondName as $i=> $char) {
        //         if ($firstName[$i] == $char) {
        //             $name[] = $char;
        //             continue;
        //         }
        //         break;
        //     }

        //     //$filename = str_replace("-".$key,'',);
        //     $outputFile = dirname($file[1]) . DIRECTORY_SEPARATOR . implode('', $name) . '.mp4' ;
        // }

        // // utmdd($outputFile);
        // $video->concat([$file[0],$tmpFile])->saveFromSameCodecs($outputFile, true);
        // // $r = $video->getFFMpegDriver()->getProcessRunner();
        // // utmdump($r);
        // unlink($tmpFile);
        // // unlink($outputFile);
        // //echo $exec->stdout;
        // return 1;
    }

    public function print()
    {
        // utminfo(func_get_args());
    }
}
