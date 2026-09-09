<?php
namespace Mediatag\Bundle\Dialog\Options;

class MenuItem
{
    protected $tag;
    protected $item = '';
    
    public function __construct($tag, string $item)
    {
        $this->tag = $tag;
        $this->item = $item;
    }
    
    public function parseItem(): string
    {
        return " '{$this->tag}' '{$this->item}'";
    }
}
