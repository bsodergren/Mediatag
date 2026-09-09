<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Radiolist extends \Mediatag\Bundle\Dialog\Widgets\Checklist
{
    
    public function __construct(string $text, int $list_height = 0, \Mediatag\Bundle\Dialog\Options\Item ...$items)
    {
        parent::__construct($text, $list_height, ...$items);
        $this->widget = 'radiolist';
    }
    
    public function item(): string
    {
        return (string) $this->output();
    }
}
