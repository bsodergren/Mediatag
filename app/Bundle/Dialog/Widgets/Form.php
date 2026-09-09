<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Form extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $items = [];
    protected $formheight = 0;
    
    public function __construct(string $text, int $formheight = 0, \Mediatag\Bundle\Dialog\Options\Field ...$items)
    {
        parent::__construct('form', $text, 0, 0);
        $this->items = $items;
        $this->formheight = $formheight;
    }
    
    public function parseToString(): string
    {
        $box_options = parent::parseToString();
        
        $box_options .= ' '.$this->formheight;
        
        foreach ($this->items as $item) {
            $box_options .= $item->parseItem();
        }
        
        return $box_options;
    }
    
    public function items(): array
    {
        $output = explode(PHP_EOL, $this->output());
        array_pop($output);
        return $output;
    }
}
