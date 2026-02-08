<?php

namespace Mediatag\Commands\Test;

use Nette\PhpGenerator\Printer;

class TestPrinter extends Printer
{
    // line length after which line wrapping occurs
    public int $wrapLength = 80;

    // indentation character, can be replaced with a sequence of spaces
    public string $indentation = "\t";

    // number of blank lines between properties
    public int $linesBetweenProperties = 0;

    // number of blank lines between methods
    public int $linesBetweenMethods = 4;

    // number of blank lines between 'use statement' groups for classes, functions, and constants
    public int $linesBetweenUseTypes = 2;

    // position of the opening curly brace for functions and methods
    public bool $bracesOnNextLine = true;

    // place a single parameter on one line, even if it has an attribute or is promoted
    public bool $singleParameterOnOneLine = false;

    // omits namespaces that do not contain any class or function
    public bool $omitEmptyNamespaces = true;

    // separator between the right parenthesis and the return type of functions and methods
    public string $returnTypeColon = ': ';
}
