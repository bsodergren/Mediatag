<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Map;

trait Lang
{
    public const CMD_DESCRIPTION = DESCRIPTION;

    public const CMD_NAME = NAME;

    public const L__MAP_CHANNEL = 'Channel Mapping';

    public const L__MAP_GENRE = 'Genre Mapping';

    public const L__MAP_KEYWORD = 'Keyword Mapping';

    public const L__MAP_EMPTY = 'reset mapping';

    public const L__MAP_DIR = 'Studio Directory';

    public const L__MAP_SHOW = 'Show genre or keyword';

    public const L__MAP_REPLACEMENT = 'Replacement strings';

    public const L__MAP_HIDE = 'Hide genre or keyword';

    public const L__MAP_SEARCH = 'Search genre or keyword';

    public const L__MAP_ADD_REPLACEMENT = 'Show genre or keyword';

    public const L__MAP_STUDIOS = 'Add a Channel name to a map';

    public const L__MAP_ARTIST = 'Add Artist name to map';

    public const L__MAP_TITLE = 'Show genre or keyword';

    public const L__MAP_DROP = 'Drop a map from the database';

    public const L__MAP_LANG = 'Language Constant to add. use -R for the text';

    public const L__MAP_FILE = 'The Language file to add the replacement too';
}
