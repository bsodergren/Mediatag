<?php
namespace Mediatag\Bundle\Dialog\Options;

class Box extends \Mediatag\Bundle\Dialog\Options
{
    
    protected $widget = '';
    protected $text = '';
    protected $height = 0;
    protected $width = 0;
    protected $output = null;
    protected $exit_code = null;
    protected $common_options = null;
    protected $dialog = null;


    public function __construct(string $widget, string $text, int $height, int $width)
    {
        parent::__construct();
        $this->widget = $widget;
        $this->text = $text;
        $this->height = $height;
        $this->width = $width;
    }
    
    public function widget(): string
    {
        return $this->widget;
    }
    
    public function text(): string
    {
        return $this->text;
    }
    
    public function height(int $value = null): int
    {
        if (is_null($value)) {
            return $this->height;
        } else {
            return $this->height = $value;
        }
    }
    
    public function width(int $value = null): int
    {
        if (is_null($value)) {
            return $this->width;
        } else {
            return $this->width = $value;
        }
    }
    
    public function parseToString(): string
    {
        return "--{$this->widget} '{$this->text}' {$this->height} {$this->width}";
    }
    
    public function output(string $value = null): string
    {
        if (is_null($value)) {
            return $this->output;
        } else {
            return $this->output = $value;
        }
    }
    
    public function exit_code(int $value = null): int
    {
        if (is_null($value)) {
            return $this->exit_code;
        } else {
            return $this->exit_code = $value;
        }
    }
    
    public function common_options(\Mediatag\Bundle\Dialog\Options\Common $value = null): \Mediatag\Bundle\Dialog\Options\Common
    {
        if (is_null($value)) {
            return $this->common_options;
        } else {
            return $this->common_options = $value;
        }
    }
    
    public function dialog(\Mediatag\Bundle\Dialog\Dialog $dialog = null): \Mediatag\Bundle\Dialog\Dialog
    {
        if (is_null($dialog)) {
            return $this->dialog;
        } else {
            return $this->dialog = $dialog;
        }
    }
}
