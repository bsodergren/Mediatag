<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Gallery;

trait Lang
{
    public const CMD_DESCRIPTION = DESCRIPTION;

    public const CMD_NAME = NAME;

    public const L__GALLERY_THUMBNAIL_CLEAN = 'Cleans up Thumbnail images';

    public const L__GALLERY_THUMBNAIL_UPDATE = 'Update Thumbnail';

    public const L__GALLERY_DURATION_UPDATE = 'Update video Duration';

    public const L__GALLERY_EMPTY = 'Emptys out the Database';

    public const L__GALLERY_ADD = 'Add something';

    public const L__GALLERY_FILEINFO_UPDATE = 'Add something';

    public const L__GALLERY_FILE_UPDATE = 'Add somethign to db';

    public const L__GALLERY_VIDEO_COUNT = '%%VID%% will be removed from DB';

    public const L__GALLERY_ASK_CONTINUE = 'Are you sure Y/N: ';
}
