<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Modules\VideoData\Data\VideoInfo;

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

    public function generateFfmpegCommand($videoFiles, $transition_type='fade', $transition_duration=2)
    {
        $files_input = [];

        foreach ($videoFiles as $index => $video) {
            $file_info[$index]    = VideoInfo::getVidInfo($video);
            $file_lengths[$index] = (int) $file_info[$index]['duration'];
            $has_audio[$index]    = true;
            $files_input          = array_merge($files_input, ['-i', $video]);
        }

        $width  = $file_info[0]['width'];
        $height = $file_info[0]['height'];

        $video_transitions      = '';
        $audio_transitions      = '';
        $last_transition_output = '0v';
        $last_audio_output      = '0:a';
        $video_length           = 0;
        $normalizer             = '';
        $scaler_default         = ',scale=w='.$width.':h='.$height.':force_original_aspect_ratio=1,pad='.$width.':'.$height.':(ow-iw)/2:(oh-ih)/2';
        $clipLength=0;

        foreach ($videoFiles as $i => $video) {
            $scaler = $i > 0 ? '' : $scaler_default;

            $normalizer .= "[{$i}:v]settb=AVTB,setsar=sar=1,fps=30{$scaler}[{$i}v];";

            if (0 == $i) {
                continue;
            }

            $video_length = (int) $file_lengths[$i - 1] - $transition_duration / 2;
            $clipLength = $clipLength + $video_length;

            $next_transition_output = 'v'.($i - 1).$i;
            $video_transitions .= "[{$last_transition_output}][{$i}v]xfade=transition={$transition_type}:duration={$transition_duration}:offset=".(int)($video_length - $transition_duration / 2)."[{$next_transition_output}];";
            $last_transition_output = $next_transition_output;

            if ($has_audio[$i - 1] && $has_audio[$i]) {
                $next_audio_output = 'a'.($i - 1).$i;
                $audio_transitions .= "[{$last_audio_output}][{$i}:a]acrossfade=d={$transition_duration}[{$next_audio_output}];";
                $last_audio_output = $next_audio_output;
            } elseif ($has_audio[$i]) {
                $last_audio_output = "{$i}:a";
            }
        }
        $this->clipLength = $clipLength + $file_lengths[$i];

        $video_transitions .= "[{$last_transition_output}]format=pix_fmts=yuv420p[final];";
        $ffmpeg_args = array_merge($files_input,
            ['-filter_complex', $normalizer.$video_transitions.
            substr($audio_transitions, 0, -1), '-map', '[final]']);

        $ffmpeg_args = array_merge($ffmpeg_args, ['-map', "[$last_audio_output]"]);

        return $ffmpeg_args;
    }
}
