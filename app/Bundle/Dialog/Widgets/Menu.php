<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Menu extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $items = [];
    protected $menu_height = 0;
    
    public function __construct(string $text, int $menu_height = 0, \Mediatag\Bundle\Dialog\Options\MenuItem ...$items)
    {
        parent::__construct('inputmenu', $text, 0, 0);
        $this->items = $items;
        $this->menu_height = $menu_height;
    }
    
    public function parseToString(): string
    {
        $box_options = parent::parseToString();
        
        $box_options .= ' '.$this->menu_height;
        
        foreach ($this->items as $item) {
            $box_options .= $item->parseItem();
        }
        
        return $box_options;
    }
    
    public function selected(): string
    {
        $selected = $this->output();
        
        return $selected;
    }
}
