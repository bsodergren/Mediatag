<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist;

trait Lang
{
    public const CMD_DESCRIPTION = DESCRIPTION;

    public const CMD_NAME = NAME;

    public const L__PLAYLIST_MAX = 'Max items to download from playlist';

    public const L__PLAYLIST_JSON = 'finds json files without a video';

    public const L__PLAYLIST_MOVE = 'Moves downloaded files';

    public const L__PLAYLIST_COMPACT = 'Compacts a playlist';

    public const L__PLAYLIST_CLEAN = 'Cleans up old PH Videos';

    public const L__PLAYLIST_DEBUG = 'Display all of the youtube-dl output';

    public const L__PLAYLIST_IGNORE = 'Ignore already downloaded';
}
