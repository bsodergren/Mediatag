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
        $this->output->getFormatter()->setStyle('id', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('text', new OutputFormatterStyle('green'));
        $this->output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('playlist', new OutputFormatterStyle('bright-magenta'));
        $this->output->getFormatter()->setStyle('download', new OutputFormatterStyle('bright-blue'));
        $this->output->getFormatter()->setStyle('file', new OutputFormatterStyle('bright-cyan'));
    }


    public function __call($method,$args)
    {
        $this->io->$method($args);
    }
    public function debug($text)
    {
        // utminfo(func_get_args());

        $formattedLine = $this->io->getErrorStyle()->info($text);
    }

    public function info(...$args)
    {
        // utminfo(func_get_args());

        $this->io->definitionList(...$args);
    }

    public function error($text)
    {
        // utminfo(func_get_args());

        $this->writeln($text, 'error');
    }

    public function write($text, $style = null)
    {
        // utminfo(func_get_args());

        if (null !== $style) {
            $text = '<' . $style . '>' . $text . '</' . $style . '>';
        }

        $this->output->write($text);
    }

    public function writeln($text, $style = null)
    {
        // utminfo(func_get_args());

        if (null !== $style) {
            $text = '<' . $style . '>' . $text . '</' . $style . '>';
        }

        $this->output->writeln($text);
    }
}
