<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Convert;

trait Lang
{
    public const L__CONVERT_ONLY = 'Set Only specified tags, comma separted';

    public const L__CONVERT_APPROVE_CHANGES = 'Approve Changes';

    public const L__CONVERT_EMPTYTAG = 'Blanks selected tag, or all tags.';

    public const L__CONVERT_LIST_CHANGES = 'Create a list of metaconverts';

    public const L__CONVERT_ALL_TAGS = 'Convert metatag with all new Info';

    public const L__CONVERT_NEWFILES_REPLACEMENT = 'Something about cache files';
}
