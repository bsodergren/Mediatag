<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
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
        $this->io        = new SymfonyStyle($input, $output);
        $this->formatter = new FormatterHelper();

        $this->output->getFormatter()->setStyle('indent', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('current', new OutputFormatterStyle('magenta'));
        $this->output->getFormatter()->setStyle('update', new OutputFormatterStyle('bright-green'));

        $this->output->getFormatter()->setStyle('id', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('text', new OutputFormatterStyle('green'));
        $this->output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('playlist', new OutputFormatterStyle('bright-magenta'));
        $this->output->getFormatter()->setStyle('download', new OutputFormatterStyle('bright-blue'));
        $this->output->getFormatter()->setStyle('file', new OutputFormatterStyle('bright-cyan'));
    }

    public function __call($method, $args)
    {
        // Mediatag::$log->info("Called {0} in ConsoleOutput",[$method]);
        // utmdd($args);
        $this->io->$method($args);
    }
}
