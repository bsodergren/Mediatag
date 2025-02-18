<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

trait LogFormat
{
    public static $ClassIgnore = ['MediaLogger', 'AbstractLogger']; // ['Symfony','Monolog','Debug',"MediaLogger"];

    public static function tracePath($only_caller = false)
    {
        $trace      = debug_backtrace();
        $classArray = [];
        $calledFile = null;
        $calledLine = null;
        foreach ($trace as $i => $row) {
            $arg = [];

            if (str_contains($row['file'], 'vendor')) {
                continue;
            }

            if (\array_key_exists('class', $row)) {
                foreach (self::$ClassIgnore as $class) {
                    if (str_contains($row['class'], $class)) {
                        if (str_contains($row['function'], 'debug')) {
                            $calledFile = self::returnTrace('file', $trace[$i]);
                        }
                        continue 2;
                    }
                }
                if ($i > 0) {
                    $line = $i - 1;
                } else {
                    $line = $i;
                }
                $calledLine = self::returnTrace('line', $trace[$line]);
                // $calledFile = self::returnTrace('file', $trace[$line]);

                $arguments         = $calledLine;
                $calledLineArray[] = [$row['class'], $row['function'], $calledLine];
                $classArray[]      = self::getClassPath($row['class'], 2).':'.$row['function'].'('.$arguments.')';
            }
        }

        if (true === $only_caller) {
            $string = implode(':', $calledLineArray[0]);

            return $string;
        }

        if (\count($classArray) > 0) {
            $classArray = array_reverse($classArray);
            foreach ($classArray as $k => $classPath) {
                [$class, $method] = explode(':', $classPath);
                $class            = str_replace('\\', '_', $class);
                $path[$class][]   = $method;
            }

            foreach ($path as $classPath => $methods) {
                $classPath = str_replace('_', '\\', $classPath);
                if (\is_array($methods)) {
                    $level      = 4;
                    $spaces     = str_repeat(' ', $level * 4);
                    $methodPath = implode("\n".$spaces.'->', $methods);
                }
                $fullPath[] = $classPath.':'.$methodPath;
            }
            $level  = 1;
            $spaces = str_repeat(' ', $level * 4);

            $string = implode(\PHP_EOL.$spaces.'->', $fullPath);

            return $string;
        }

        return '';
    }

    private static function getClassPath($class, $level = 1)
    {
        preg_match('/.*\\\\([A-Za-z]+)\\\\([A-Za-z]+)/', $class, $out);
        if (2 == $level) {
            return $out[1].'\\'.$out[2];
        }

        return $out[2];
    }

    private static function returnTrace($type, $row)
    {
        if ($row[$type]) {
            $text = $row[$type];

            if ('class' == $type) {
                $text = self::getClassPath($text, 2);
            }

            if ('file' == $type) {
                $text = basename($text);
            }

            return $text;
        }

        return null;
    }
}
