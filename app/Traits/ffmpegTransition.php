<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;

use function in_array;
use function is_array;

trait ffmpegTransition
{
    public $transition_types = [
        'circleclose', 'circlecrop', 'circleopen', 'coverdown', 'coverleft', 'coverright', 'coverup',
        'diagbl', 'diagbr', 'diagtl', 'diagtr', 'dissolve', 'distance', 'fade', 'fadeblack', 'fadegrays',
        'fadewhite', 'hblur', 'hlslice', 'hlwind', 'horzclose', 'horzopen', 'hrslice', 'hrwind', 'pixelize',
        'radial', 'rectcrop', 'revealdown', 'revealleft', 'revealright', 'revealup', 'slidedown', 'slideleft',
        'slideright', 'slideup', 'smoothdown', 'smoothleft', 'smoothright', 'smoothup', 'squeezeh', 'squeezev',
        'vdslice', 'vdwind', 'vertclose', 'vertopen', 'vuslice', 'vuwind', 'wipebl', 'wipebr', 'wipedown',
        'wipeleft', 'wiperight', 'wipetl', 'wipetr', 'wipeup', 'zoomin',
    ];
    private $default_transition = 'radial';

    private function getTransition($transition_type)
    {
        $return = $this->default_transition;

        if (is_array($transition_type)) {
            $key        = array_rand($transition_type);
            $transition = $transition_type[$key];

            if ('random' == $transition) {
                $key        = array_rand($this->transition_types);
                $transition = $this->transition_types[$key];
            }
        }

        if (in_array($transition, $this->transition_types)) {
            $return = $transition;
        }
        Mediatag::$output->writeln('<info>Using transition '.$return.' </info>');

        return $return;
    }

    public function generateFfmpegCommand($videoFiles, $transition_type, $transition_duration)
    {
        $files_input = [];

        // if ('none' == $transition_type[0]) {
        //     $ffmpeg = FFMpeg::create();

        //     $video  = $ffmpeg->open($videoFiles[0]);
        //     $format = new X264();

        //     $format->setAudioCodec('libmp3lame');
        //     $video
        //         ->concat($videoFiles)
        //         ->saveFromDifferentCodecs($format, $this->clipName);
        //     utmdd($this->clipName);

        //     return true;
        // }
        $frame_count = 0;
        foreach ($videoFiles as $index => $video) {
            $file_info[$index]    = VideoFileInfo::getVidInfo($video);
            $file_lengths[$index] = ($file_info[$index]['duration'] / 1000);
            $has_audio[$index]    = true;
            $files_input          = array_merge($files_input, ['-i', $video]);
            $frame_count += $file_info[$index]['frame_count'];
        }
        $video_transitions      = '';
        $audio_transitions      = '';
        $last_transition_output = '0v';
        $last_audio_output      = '0:a';
        $video_length           = 0;
        $offset                 = 0;
        $normalizer             = '';

        $width  = (int) $file_info[0]['width'];
        $height = (int) $file_info[0]['height'];

        $scaler_default = ",scale=w={$width}:h={$height}:force_original_aspect_ratio=1,pad={$width}:{$height}:(ow-iw)/2:(oh-ih)/2";

        foreach ($videoFiles as $i => $video) {
            $transition = $this->getTransition($transition_type);
            // $scaler     = $i > 0 ? ',scale=w='.$file_info[$i]['width'].':h='.$file_info[$i]['height'].':force_original_aspect_ratio=1,pad='.$file_info[$i]['width'].':'.$file_info[$i]['height'].':(ow-iw)/2:(oh-ih)/2' : '';

            $scaler = $i > 0 ? $scaler_default : '';
            $normalizer .= "[{$i}:v]settb=AVTB,setsar=sar=1,fps=30{$scaler}[{$i}v];";

            if (0 == $i) {
                continue;
            }

            $video_length = $file_lengths[$i - 1] - $transition_duration / 2;
            $offset += $video_length;
            $next_transition_output = 'v'.($i - 1).$i;
            $video_transitions .= "[{$last_transition_output}][{$i}v]xfade=transition={$transition}:duration={$transition_duration}:offset=".($offset - $transition_duration / 2)."[{$next_transition_output}];";
            $last_transition_output = $next_transition_output;

            if ($has_audio[$i - 1] && $has_audio[$i]) {
                $next_audio_output = 'a'.($i - 1).$i;
                $audio_transitions .= "[{$last_audio_output}][{$i}:a]acrossfade=d={$transition_duration}[{$next_audio_output}];";
                $last_audio_output = $next_audio_output;
            } elseif ($has_audio[$i]) {
                $last_audio_output = "{$i}:a";
            }
        }
        $this->clipLength = $frame_count;
        // utmdd($this->clipLength, $file_lengths[$i]);
        $video_transitions .= "[{$last_transition_output}]format=pix_fmts=yuv420p[final];";
        // $normalizer        = str_replace(';', ';'.\PHP_EOL, $normalizer);
        // $video_transitions = str_replace(';', ';'.\PHP_EOL, $video_transitions);
        // $audio_transitions = str_replace(';', ';'.\PHP_EOL, $audio_transitions);
        $ffmpeg_args = array_merge($files_input,
            ['-filter_complex', $normalizer.$video_transitions
            .substr($audio_transitions, 0, -1), '-map', '[final]']);

        $ffmpeg_args = array_merge($ffmpeg_args, ['-map', "[$last_audio_output]"]);

        // utmdump($ffmpeg_args);
        return $ffmpeg_args;
    }
}
