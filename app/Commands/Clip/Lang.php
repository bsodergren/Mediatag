<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\Mediatag;

trait Lang
{
    public const CMD_DESCRIPTION = DESCRIPTION;

    public const CMD_NAME        = NAME;
    public const L__CLIP_CREATE_CLIPS = "Create Clips from files in Cur Directory";
    public const L__CLIP_CREATE_COMP = "Create Compilation from clips made from Cur Directory";
    public const L__CLIP_DELETE_CLIPS = "Delete Clips from Cur Directory";
    public const L__CLIP_VIDEO_COUNT = '%%VID%% Clips will be deleted';

    public const L__CLIP_ASK_CONTINUE = 'Are you sure Y/N: ';
    public const L__CLIP_DELETE_YES = ' Delete clips in path';
}
