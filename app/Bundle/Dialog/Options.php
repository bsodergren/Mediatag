<?php
namespace Mediatag\Bundle\Dialog;

class Options
{
    
    protected $options = [];
    
    public function __construct(?array ...$option)
    {
        if (count($option) > 0) {
            foreach ($option as $item) {
                $this->options[$item[0]] = $item[1];
            }
        }
    }
    
    public function parseToString(): string
    {
        $str = '';
        
        foreach ($this->options as $key => $value) {
            $key = $this->convertToDialogOption($key);
            $value = $this->convertToDialogOptionValue($value);
            if ($value === '') {
                $str .= " --$key";
            } else {
                $str .= " --$key $value";
            }
        }
        
        return $str;
    }
    
    protected function convertToDialogOptionValue($value): string
    {
        if (is_array($value)) {
            $value = join(' ', $value);
        } elseif (is_string($value)) {
            $value = "'$value'";
        } elseif (is_bool($value)) {
            $value = '';
        }
        
        return  $value;
    }

    protected function convertToInternalOption(string $name): string
    {
        return str_replace('-', '_', $name);
    }
    
    protected function convertToDialogOption(string $name): string
    {
        return str_replace('_', '-', $name);
    }

    public function __set($name, $value): void
    {
        $this->options[$this->convertToInternalOption($name)] = $value;
    }
    
    public function __get($name)
    {
        $key = $this->convertToInternalOption($name);
        
        if (key_exists($key, $this->options)) {
            return $this->options[$key];
        } else {
            return null;
        }
    }
}
