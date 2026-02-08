<?php

namespace Mediatag\Traits;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use ReflectionProperty;

trait DynamicProperty
{
    private array $data = [];

    public function __set($name, $value): void
    {
        $prefix                     = Strings::after(__CLASS__, '\\', -1);
        $this->data[$prefix][$name] = $value;
    }

    public function __get(string $name)
    {
        $prefix = Strings::after(__CLASS__, '\\', -1);

        return $this->data[$prefix][$name] ?? null;
    }
}
