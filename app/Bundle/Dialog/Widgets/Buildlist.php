<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Buildlist extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $items = [];
    protected $list_height = 0;
    
    public function __construct(string $text, int $list_height = 0, \Mediatag\Bundle\Dialog\Options\Item ...$items)
    {
        parent::__construct('buildlist', $text, 0, 0);
        $this->items = $items;
        $this->list_height = $list_height;
    }
    
    public function parseToString(): string
    {
        $box_options = parent::parseToString();
        
        $box_options .= ' '.$this->list_height;
        
        foreach ($this->items as $item) {
            $box_options .= $item->parseItem();
        }
        
        return $box_options;
    }
    
    public function items(): array
    {
        $selected = $this->output();
        if ($this->common_options->separate_output === true) {
            if ($this->common_options->separator === null) {
                $separator = PHP_EOL;
            } else {
                $separator = $this->common_options->separator;
            }

            $selected = explode($separator, $selected);
            array_pop($selected);
        } else {
            $selected = explode(" ", $selected);
        }

        return $selected;
    }
}
