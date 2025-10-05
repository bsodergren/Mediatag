<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsoleOutput
{
    public $formatter;

    public $output;

    public $io;

    public function __construct($output, $input)
    {
        // utminfo();

        /*
        black, red, green, yellow, blue,
        magenta, cyan, white, gray,
        bright-red, bright-green, bright-yellow,
        bright-blue, bright-magenta, bright-cyan
        bright-white

        bold, underscore, blink, reverse
        */

        $this->output    = $output;
        $this->io        = new SymfonyStyle($input, $this->output);
        $this->formatter = new FormatterHelper;

        $this->output->getFormatter()->setStyle('indent', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('current', new OutputFormatterStyle('magenta'));
        $this->output->getFormatter()->setStyle('update', new OutputFormatterStyle('bright-green'));

        $this->output->getFormatter()->setStyle('id', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('text', new OutputFormatterStyle('green'));
        $this->output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('playlist', new OutputFormatterStyle('bright-magenta'));
        $this->output->getFormatter()->setStyle('download', new OutputFormatterStyle('blue'));
        $this->output->getFormatter()->setStyle('file', new OutputFormatterStyle('bright-cyan'));
        // Mediatag::$output = $this->output;

        $styleArray = $this->output->getFormatter()->getStyles();

        if ($this->output instanceof ConsoleOutputInterface) {
            $this->output = $output->getErrorOutput();
            foreach ($styleArray as $name => $obj) {
                $this->output->getFormatter()->setStyle($name, $obj);
            }
        }

        //    utmdd("something else");
        //
    }

    public function __call($method, $args)
    {
        // Mediatag::$log->info("Called {0} in ConsoleOutput",[$method]);
        //  utmdd([$method,$args]);
        $this->io->$method(...$args);
    }

    public function table($args)
    {
        $header = $args[0];
        unset($args[0]);
        // utmdd($args);

        $this->io->table([$header], $args);
    }
}
