<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Bundle\Dialog\Options;

class Item
{
    protected $tag;

    protected $item = '';

    protected $status = false;

    public function __construct($tag, string $item, bool $status = false)
    {
        $this->tag = $tag;
        $this->item = $item;
        $this->status = $status;
    }

    public function parseItem(): string
    {
        $status = ($this->status === true) ? 'on' : 'off';

        return " '{$this->tag}' '{$this->item}' $status";
    }
}
