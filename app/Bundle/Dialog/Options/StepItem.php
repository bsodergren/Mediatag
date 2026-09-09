<?php
namespace Mediatag\Bundle\Dialog\Options;

class StepItem
{
    const RESULT_SUCCESS = 0;
    const RESULT_FAILED = 1;
    const RESULT_PASSED = 2;
    const RESULT_COMPLETED = 3;
    const RESULT_CHECKED = 4;
    const RESULT_DO = 5;
    const RESULT_SKIPPED = 6;
    const RESULT_PROGRESS = 7;
    const RESULT_HIDDEN = 8;
    const RESULT_NA = 9;
    
    protected $tag;
    protected $percent = 0;
    protected $running = false;
    
    public function __construct($tag, int $percent = 8)
    {
        $this->tag = $tag;
        $this->percent = $percent;
    }
    
    public function running(bool $running = null): bool
    {
        if (is_null($running)) {
            return $this->running;
        } else {
            return $this->running = $running;
        }
    }
    
    public function parseItem(): string
    {
        $running = ($this->running)? '-' : '';
        return " '{$this->tag}' '$running{$this->percent}'";
    }
    
    public function percent(int $percent): void
    {
        $this->percent = $percent;
        return;
    }
}
