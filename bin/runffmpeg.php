#!/usr/bin/env php
<?php
/**
 * Command like Metatag writer for video files.
 */

use Symfony\Component\Process\Process;

define('__ROOT_DIRECTORY__', dirname(realpath($_SERVER['SCRIPT_FILENAME']), 2));
define('__CONFIG_LIB__', __ROOT_DIRECTORY__.'/config');

require __ROOT_DIRECTORY__.'/bootstrap.php';

$ffmpeg_cmd = [
    CONFIG['FFMPEG_CMD'],
    // '-hide_banner',
    // '-v', 'info',
];

$ffmpeg_videos = [
    '-i','/media/Videos/Plex/Clips/Studios/Thagson/MMF/SheLikesThreesomes3_s03_DahliaSky_MarkZane_720p_h264_Blowjob_0.mp4',
    '-i', '/media/Videos/Plex/Clips/Studios/Thagson/MMF/SheLikesThreesomes3_s03_DahliaSky_MarkZane_720p_h264_cumshot_1.mp4',
    '-i', '/media/Videos/Plex/Clips/Studios/Thagson/MMF/FamilyTabooIndecentYoungsters2_s02_AnnadeVille_NikkiNuttz_720p_Blowjob_0.mp4',
];

$ffmpeg_options = [
    // '-filter_complex',
    // '"[0:v]settb=AVTB,fps=30/1[v0];[1:v]settb=AVTB,fps=30/1[v1];[2:v]settb=AVTB,fps=30/1[v2];"',
    '-filter_complex',
    '"[0:v][1:v]xfade=transition=slideleft:duration=2:offset=216.08[vfade1];[vfade1][2:v]xfade=transition=slideleft:duration=2:offset=457.16[vfade2];[vfade2]format=yuv420p[vout];"',
];
$ffmpeg_map = [
    '-map', '[vout]',  
    // '-map', '"[v0]"', 
    // '-map', '"[v1]"',  
    // '-map', '"[v2]"',
];

$ffmpeg_output = ['-y',  '-codec', 'copy',
    '"/media/Videos/Plex/XXX/Studios/Home Videos/Compilation/test.mp4"',
];

$cmd = array_merge($ffmpeg_cmd, $ffmpeg_videos, $ffmpeg_options, $ffmpeg_map, $ffmpeg_output);

$process = new Process($cmd);
$out     = $process->getCommandLine();
$process->run();
echo $process->getErrorOutput();
utmdd($out);
