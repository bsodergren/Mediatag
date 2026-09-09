<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Treeview extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $nodes = [];
    protected $list_height = 0;
    
    public function __construct(string $text, int $list_height = 0, \Mediatag\Bundle\Dialog\Options\TreeNode ...$nodes)
    {
        parent::__construct('treeview', $text, 0, 0);
        $this->nodes = $nodes;
        $this->list_height = $list_height;
    }
    
    public function parseToString(): string
    {
        $box_options = parent::parseToString();
        
        $box_options .= ' '.$this->list_height;
        
        foreach ($this->nodes as $node) {
            $box_options .= $node->parseItem();
        }
        
        return $box_options;
    }
    
    public function node()
    {
        return $this->output();
    }
    
    public function append(\Mediatag\Bundle\Dialog\Options\TreeNode $node): void
    {
        $this->nodes[] = $node;
    }
}
