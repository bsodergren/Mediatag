<?php
//exit('Not work!');
namespace Mediatag\Bundle\Dialog\Widgets;

class Mixedgauge extends \Mediatag\Bundle\Dialog\Options\Box
{
    
    
    protected $percent = 0;
    protected $items = [];
    public function __construct(string $text, int $percent = 0, \Mediatag\Bundle\Dialog\Options\StepItem ...$items)
    {
        parent::__construct('mixedgauge', $text, 0, 0);
        $this->percent = $percent;
        $this->items = $items;
    }
    
    public function parseToString(): string
    {
        $box_options = parent::parseToString();
        
        $box_options .= " {$this->percent}";
        
        foreach ($this->items as $item) {
            $box_options .= $item->parseItem();
        }
        
        return $box_options;
    }
    
    public function items(): array
    {
        return $this->items;
    }
    
    public function percent(int $percent): void
    {
        $this->percent = $percent;
        return;
    }
}
