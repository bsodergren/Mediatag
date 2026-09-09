<?php
namespace Mediatag\Bundle\Dialog\Options;

class TreeNode
{
    protected $tag;
    protected $item = '';
    protected $status = false;
    protected $parent = null;
    protected $children = [];
    
    
    public function __construct($tag, string $item, bool $status = false, TreeNode ...$child)
    {
        $this->tag = $tag;
        $this->item = $item;
        $this->status = $status;
        foreach ($child as $node) {
            $node->parent($this);
            $this->children[] = $node;
        }
    }
    
    public function parent(TreeNode $parent): void
    {
        $this->parent = $parent;
    }

    public function depth(): int
    {
        if (is_null($this->parent)) {
            return 0;
        } else {
            return $this->parent->depth() + 1;
        }
    }

    public function parseItem(): string
    {
        $status = ($this->status === true)? 'on' : 'off';
        $depth = $this->depth();
        
        $children = '';
        foreach ($this->children as $child) {
            $children .= "{$child->parseItem()}";
        }
        
        return " '{$this->tag}' '{$this->item}' $status $depth".$children;
    }
    
    public function append(\Mediatag\Bundle\Dialog\Options\TreeNode $node): void
    {
        $this->children[] = $node;
    }
}
