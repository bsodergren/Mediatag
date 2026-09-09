<?php
namespace Mediatag\Bundle\Dialog\Widgets;

class Rangebox extends \Mediatag\Bundle\Dialog\Options\Box
{
    protected $min_value = 0;
    protected $max_value = 0;
    protected $default_value = 0;
    public function __construct(string $text, int $min_value, int $max_value, int $default_value)
    {
        parent::__construct('rangebox', $text, 0, 0);
        $this->min_value = $min_value;
        $this->max_value = $max_value;
        $this->default_value = $default_value;
    }
    
    public function parseToString(): string
    {
        return "--{$this->widget} '{$this->text}' {$this->height} {$this->width} {$this->min_value} {$this->max_value} {$this->default_value}";
    }
    
    public function min_value(int $value = null): int
    {
        if (is_null($value)) {
            return $this->min_value;
        } else {
            return $this->min_value = $value;
        }
    }
    
    public function max_value(int $value = null): int
    {
        if (is_null($value)) {
            return $this->max_value;
        } else {
            return $this->max_value = $value;
        }
    }
    
    public function default_value(int $value = null): int
    {
        if (is_null($value)) {
            return $this->default_value;
        } else {
            return $this->default_value = $value;
        }
    }
    
    public function value(): int
    {
        return (int) $this->output();
    }
}
