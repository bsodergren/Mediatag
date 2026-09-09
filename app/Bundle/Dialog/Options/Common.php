<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Bundle\Dialog\Options;

class Common extends \Mediatag\Bundle\Dialog\Options
{
    public function __construct(?array ...$option)
    {
        parent::__construct(...$option);
    }
}
