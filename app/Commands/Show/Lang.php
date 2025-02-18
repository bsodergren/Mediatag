<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

trait Lang
{
    public const CMD_DESCRIPTION = DESCRIPTION;

    public const CMD_NAME = NAME;

    public const L__SHOW_MISSING = 'Create a list of files with a missing tag';

    public const L__SHOW_NEW = 'List all recently added files';

    public const L__SHOW_DUPES = 'List all recently added files';

    public const L__SHOW_ONLY = 'Show Only specified tags, comma separted';

    public const L__SHOW_PLAYLIST = 'Create a playlist from Porhub files';
}
