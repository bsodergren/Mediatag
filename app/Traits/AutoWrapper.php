<?php

namespace Mediatag\Traits;

use BadMethodCallException;
use ReflectionMethod;

trait AutoWrapper
{
    // Function to run before every method
    private function beforeEveryMethod($method, $args)
    {
        // utmdump($method, $args);
    }

    // Intercept calls to undefined/inaccessible methods
    public function __call($name, $arguments)
    {
        // Check if the method actually exists and is public
        // utmdump(['name' => $name]);
        if (method_exists($this, $name)) {
            $refMethod = new ReflectionMethod($this, $name);

            // If it's public and declared in this class, intercept
            if ($refMethod->isPublic() && $refMethod->getDeclaringClass()->getName() === get_class($this)) {
                $this->beforeEveryMethod($name, $arguments);

                return $refMethod->invokeArgs($this, $arguments);
            }
        }
        throw new BadMethodCallException("Method '$name' does not exist.");
    }

    // For static methods
    public static function __callStatic($name, $arguments)
    {
        if (method_exists(static::class, $name)) {
            $refMethod = new ReflectionMethod(static::class, $name);

            if ($refMethod->isPublic() && $refMethod->getDeclaringClass()->getName() === static::class) {
                echo "[LOG] Calling static '$name' with arguments: ";
                print_r($arguments);

                return $refMethod->invokeArgs(null, $arguments);
            }
        }
        throw new BadMethodCallException("Static method '$name' does not exist.");
    }
}
