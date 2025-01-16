<?php
namespace Mediatag\Modules\VideoData\Data\preview;
/**
 * Command like Metatag writer for video files.
 */

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;

class GeneratePreview
{
    public function __construct()
    {
    }

    public function processVideo($opts)
    {
        // $log = $opts['log'] ?? $noop;

        // general output options
        $quality = $opts['quality'] ?? 2;
        $width   = $opts['width']   ?? null;
        $height  = $opts['height']  ?? null;
        $input   = $opts['input']   ?? null;
        $output  = $opts['output']  ?? null;

        $numFrames        = $opts['numFrames']        ?? null;
        $numFramesPercent = $opts['numFramesPercent'] ?? 0.05;

        // image strip options
        $padding = $opts['padding'] ?? 0;
        $margin  = $opts['margin']  ?? 0;
        $cols    = $opts['cols']    ?? null;
        $rows    = $opts['rows']    ?? 1;
        $color   = $opts['color']   ?? null;

        // gif options
        $gifski = $opts['gifski'] ?? [
            'fps'     => 10,
            'quality' => 80,
            'fast'    => false,
        ];

        $ffprobe        = FFProbe::create();
        $info           = $ffprobe->streams($input)->first();
        $numFramesTotal = (int) $info->get('nb_frames');
        $ext            = pathinfo($output, \PATHINFO_EXTENSION);
        $isGIF          = ('gif' === strtolower($ext));

        utmdump($isGIF);
        $numFramesToCapture = $numFrames ?? $numFramesPercent * $numFramesTotal;
        if (!$isGIF && $rows > 0 && $cols > 0) {
            $numFramesToCapture = $rows * $cols;
        }
        $numFramesToCapture = max(1, min($numFramesTotal, (int) $numFramesToCapture));
        $nthFrame           = (int) ($numFramesTotal / $numFramesToCapture);

        $tempDir    = $isGIF ? sys_get_temp_dir().'/'.uniqid('temp_', true) : null;

     

        $tempOutput = $isGIF ? $tempDir.'/frame-%d.png' : $output;
   utmdump([$tempDir,$tempOutput]);
        $result = [
            'output'    => $output,
            'numFrames' => $numFramesToCapture,
        ];

        $scale = null;
        $tile  = null;

        if ($width && $height) {
            $result['width']  = (int) $width;
            $result['height'] = (int) $height;
            $scale            = "scale={$width}:{$height}";
        } elseif ($width) {
            $result['width']  = (int) $width;
            $result['height'] = (int) ($info->get('height') * $width / $info->get('width'));
            $scale            = "scale={$width}:-1";
        } elseif ($height) {
            $result['height'] = (int) $height;
            $result['width']  = (int) ($info->get('width') * $height / $info->get('height'));
            $scale            = "scale=-1:{$height}";
        } else {
            $result['width']  = $info->get('width');
            $result['height'] = $info->get('height');
        }

        if (!$isGIF) {
            $numRows = max(1, (int) $rows);
            $numCols = max(1, (int) (($cols ?? $numFramesToCapture) / $numRows));

            $tileOptions = [
                "tile={$numCols}x{$numRows}",
                $padding ? "padding={$padding}" : null,
                $margin ? "margin={$margin}" : null,
                $color ? "color={$color}" : null,
            ];
            $tile = implode(':', array_filter($tileOptions));

            $result['rows']    = $numRows;
            $result['cols']    = $numCols;
            $result['padding'] = $padding;
            $result['margin']  = $margin;
        }

        $filter = implode(',', array_filter([
            "select=not(mod(n\\,{$nthFrame}))",
            $scale,
            $tile,
        ]));

        $ffmpeg = FFMpeg::create();
        $video  = $ffmpeg->open($input);
        $video->filters()->custom("{$filter}");
        $video->save(new X264(), $tempOutput);

        if ($isGIF) {
            $framePattern = str_replace('%d', '*', $tempOutput);
            $escapePath   = function ($arg) {
                return escapeshellarg($arg);
            };

            $params = [
                '-o', $escapePath($output),
                '--fps', $gifski['fps'],
                $gifski['fast'] ? '--fast' : null,
                '--quality', $gifski['quality'],
                '--quiet',
                $escapePath($framePattern),
            ];
            $params = array_filter($params);

            $executable = getenv('GIFSKI_PATH') ?: 'gifski';
            $cmd        = implode(' ', array_merge([$executable], $params));
            utmdump($cmd);
            // if ($log) $log($cmd);

            shell_exec($cmd);
            array_map('unlink', glob("{$tempDir}/*"));
            rmdir($tempDir);
        }

        return $result;
    }
}
