<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;

trait Lang
{
 

    public const L__UPDATE_ONLY            = 'Set Only specified tags, comma separted';

    public const L__UPDATE_APPROVE_CHANGES = 'Approve Changes';

    public const L__UPDATE_EMPTYTAG        = 'Blanks selected tag, or all tags.';

    public const L__UPDATE_LIST_CHANGES    = 'Create a list of metaupdates';

    public const L__UPDATE_ALL_TAGS        = 'Update metatag with all new Info';
	public const L__UPDATE_NEWFILES_REPLACEMENT            = 'Something about cache files';
}