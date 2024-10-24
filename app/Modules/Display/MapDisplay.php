<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;


use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class MapDisplay
{
    public $output;

    public $fileCountSection;

    public $fileInfoSection;

    public $MetaBlockSection;

    public $processOutput;

    /**
     * @var int
     */
    public $displayTimer = 500000;

    public $table;

    protected $formatter;

    public function __construct(OutputInterface $output)
    {
        utminfo(func_get_args());

        $this->formatter = new FormatterHelper();
        $outputStyle     = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('indent', $outputStyle);
        $this->output    = $output;
    }

    public function drawTable($data)
    {
        utminfo(func_get_args());

        $output      = $this->output;
        $section     = $output->section();
        $this->table = new Table($section);
        $this->table->setStyle('box');
        $this->table->setHeaders(array_keys($data[0]));
        $this->table->addrow(new TableSeparator());
        $this->table->render();
    }

    public function addRow($data)
    {
        utminfo(func_get_args());

        $idx = 1;
        foreach ($data as $k => $row) {
            $row['id'] = $idx;
            $this->table->appendRow($row);
            ++$idx;
        }
    }
}
